package adriantam18.crowdcontrol.BranchMaps;

import android.Manifest;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.graphics.Bitmap;
import android.graphics.Color;
import android.location.Location;
import android.os.Build;
import android.os.Bundle;
import android.support.annotation.NonNull;
import android.support.v4.app.ActivityCompat;
import android.support.v4.app.DialogFragment;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.AdapterView;
import android.widget.EditText;
import android.widget.ListView;

import com.google.android.gms.common.ConnectionResult;
import com.google.android.gms.common.api.GoogleApiClient;
import com.google.android.gms.common.api.PendingResult;
import com.google.android.gms.common.api.ResultCallback;
import com.google.android.gms.common.api.Status;
import com.google.android.gms.location.LocationListener;
import com.google.android.gms.location.LocationRequest;
import com.google.android.gms.location.LocationServices;
import com.google.android.gms.location.LocationSettingsRequest;
import com.google.android.gms.location.LocationSettingsResult;
import com.google.android.gms.location.LocationSettingsStatusCodes;
import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.BitmapDescriptorFactory;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.MarkerOptions;
import com.google.maps.android.ui.IconGenerator;

import java.util.ArrayList;
import java.util.List;

import adriantam18.crowdcontrol.ConfirmDialog;
import adriantam18.crowdcontrol.ConfirmDialogListener;
import adriantam18.crowdcontrol.Model.BranchData;
import adriantam18.crowdcontrol.Branch.BranchPresenter;
import adriantam18.crowdcontrol.Branch.BranchView;
import adriantam18.crowdcontrol.Crowd.CrowdList;
import adriantam18.crowdcontrol.R;
import butterknife.BindView;
import butterknife.ButterKnife;

public class MapsActivity extends AppCompatActivity implements
        OnMapReadyCallback,
        GoogleApiClient.ConnectionCallbacks,
        GoogleApiClient.OnConnectionFailedListener,
        LocationListener,
        ConfirmDialogListener,
        BranchView{

    private GoogleMap mMap; // Might be null if Google Play services APK is not available.

    /** Adapter that will connect mAddresses and the list view below the maps fragment. */
    private MapsListAdapter mMapsAdapter;

    /** LisView that will display data about branches that are close to user's location. */
    @BindView(R.id.branch_list)
    ListView mListView;

    /** Google Play services API client that will allow use of fused location provider. */
    private GoogleApiClient mGoogleApiClient;

    /** Current location of the user. */
    private Location mCurrLocation;

    /** Company that the user is interested in getting locations for. */
    private String mCompany;

    /** Used in conjunction with the FusedLocationProviderApi. */
    private LocationRequest mLocationRequest;

    private static final int LOCATION_PERMISSION_REQUEST_CODE = 1010;
    private static final String COMPANY_KEY = "company";

    private static final String ACTION_PERMISSION = "permission";
    private static final String ACTION_CONFIRM = "confirm";

    @BindView(R.id.enter_comp)
    EditText mSearch;

    private BranchPresenter mPresenter;

    private boolean mPermissionDenied = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_maps);
        ButterKnife.bind(this);

        createLocationRequest();

        mMapsAdapter = new MapsListAdapter(this, new ArrayList<BranchData>());
        mListView.setAdapter(mMapsAdapter);
        setListViewListenter();

        if(savedInstanceState == null) {
            Intent intent = getIntent();
            mCompany = intent.getStringExtra("company");
        }else{
            mCompany = savedInstanceState.getString(COMPANY_KEY);
        }

        mSearch.setText(mCompany.trim());
        mSearch.clearFocus();

        mPresenter = new BranchPresenter(this);

        SupportMapFragment mapFragment = (SupportMapFragment) getSupportFragmentManager()
                .findFragmentById(R.id.map);
        mapFragment.getMapAsync(this);
    }

    @Override
    public void onSaveInstanceState(Bundle outState){
        super.onSaveInstanceState(outState);

        outState.putString(COMPANY_KEY, mSearch.getText().toString());
    }

    @Override
    protected void onResumeFragments() {
        super.onResumeFragments();
        if(mPermissionDenied) {
            DialogFragment fragment = ConfirmDialog.newInstance("Location Permission", "Cannot display map without permission", ACTION_CONFIRM);
            fragment.show(getSupportFragmentManager(), "DIALOG");
            mPermissionDenied = false;
        }
    }

    @Override
    protected void onPause(){
        super.onPause();
        if(mGoogleApiClient != null && mGoogleApiClient.isConnected()) {
            stopLocationUpdates();
        }
    }

    @Override
    protected void onDestroy(){
        super.onDestroy();
    }

    @Override
    protected void onStop(){
        if(mGoogleApiClient != null && mGoogleApiClient.isConnected()){
            stopLocationUpdates();
            mGoogleApiClient.disconnect();
        }
        super.onStop();
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu){
        getMenuInflater().inflate(R.menu.menu_maps, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem menuItem){
        int id = menuItem.getItemId();

        if(id == R.id.refresh){
            fetchData();
        }

        return super.onOptionsItemSelected(menuItem);
    }

    @Override
    public void onMapReady(GoogleMap map){
        mMap = map;
        enableLocation();
    }

    @Override
    public void onLocationChanged(Location location){
        mCurrLocation = location;
        fetchData();
        stopLocationUpdates();
    }

    @Override
    public void onConnected(Bundle connectionHunt){
        //Once connected to google api, check user's location settings
        setLocationCheck();
    }

    @Override
    public void onConnectionFailed(ConnectionResult result){
        Log.e("connection failed", result.toString());
        showAlertDialog("Something went wrong. Try again later.");
    }

    @Override
    public void onConnectionSuspended(int result){
        Log.e("connection suspended", Integer.toString(result));
        showAlertDialog("Something went wrong. Try again later.");
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data){
        super.onActivityResult(requestCode, resultCode, data);
        //If the user turned on location services, check to see if current location is set
        //otherwise request location from location services
        if(resultCode == RESULT_OK){
            if(setCurrLocation())
                fetchData();
            else
                startLocationUpdates();
        }else{
            showAlertDialog("To use this feature, the app needs access to your location.");
        }
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull String[] permissions,
                                           @NonNull int[] grantResults){
        if(requestCode == LOCATION_PERMISSION_REQUEST_CODE){
            if(permissions.length == 1 && permissions[0].equals(Manifest.permission.ACCESS_FINE_LOCATION)
                    && grantResults[0] == PackageManager.PERMISSION_GRANTED){
                enableLocation();
            }else{
                mPermissionDenied = true;
            }
        }
    }

    @Override
    public void onConfirmClick(String action){
        switch (action){
            case ACTION_PERMISSION:
                ActivityCompat.requestPermissions(this, new String[]{Manifest.permission.ACCESS_FINE_LOCATION},
                        LOCATION_PERMISSION_REQUEST_CODE);
                break;
            default:
                break;
        }
    }

    /**
     * This function is responsible for adding the markers on the map fragment where close locations
     * are located. It also marks the location of the user. Also makes the list of branches visible if there are
     * branches that are close to the user.
     */
    @Override
    public void showData(List<BranchData> closeBranches){
        mMapsAdapter.replaceData(closeBranches);
        mMap.clear();
        if(closeBranches.isEmpty()) {
            showAlertDialog("Could not find any locations close to you.");
        }else {
            LatLng mypos = new LatLng(mCurrLocation.getLatitude(), mCurrLocation.getLongitude());
            setMapMarker(mypos, "Your Location", Color.GREEN, 0);
            mMap.moveCamera(CameraUpdateFactory.newLatLngZoom(mypos, 12));

            //Will be used as label on map marker, User location is index 0
            int index = 1;
            for (BranchData branchData : closeBranches) {
                LatLng latLng = new LatLng(Double.parseDouble(branchData.getLat()), Double.parseDouble(branchData.getLng()));
                setMapMarker(latLng, branchData.getAddress(), Color.RED, index++);
            }
        }
    }

    /**
     * Places a marker on a position on the map
     * @param pos coordinates in degrees of a location
     * @param title title for the map marker
     * @param color color of the marker
     * @param index number of the marker on the accompanying list
     */
    private void setMapMarker(LatLng pos, String title, int color, int index){
        IconGenerator iconGen = new IconGenerator(this);
        iconGen.setColor(color);
        iconGen.setTextAppearance(R.style.mapLabel);

        Bitmap icon;
        if(index != 0){
            icon = iconGen.makeIcon(Integer.toString(index));
        }else{
            icon = iconGen.makeIcon();
        }

        MarkerOptions markerOptions = new MarkerOptions()
                .position(pos)
                .title(title)
                .icon(BitmapDescriptorFactory.fromBitmap(icon));

        mMap.addMarker(markerOptions);
    }

    @Override
    public void showRooms(BranchData branch){
        Intent intent = new Intent(this, CrowdList.class);
        intent.putExtra("company", mCompany);
        intent.putExtra("address", branch.getAddress());
        intent.putExtra("branch", branch.getId());
        startActivity(intent);
    }

    @Override
    public void showError(String message){
        showAlertDialog(message);
    }

    /**
     * Sets the listener for the list view below the map fragment so when the user clicks
     * a branch they are taken to the activity responsible for displaying rooms for that branch.
     */
    private void setListViewListenter(){
        mListView.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                mPresenter.getRooms(position);
            }
        });
    }

    /**
     * Opens up an alert dialog to display a message
     * @param message the message to display
     */
    private void showAlertDialog(String message){
        ConfirmDialog dialog = ConfirmDialog.newInstance("", message, ACTION_CONFIRM);
        dialog.show(getSupportFragmentManager(), "Dialog");
    }

    public void mapSearchClicked(View view){
        if(mSearch.getText().toString().isEmpty()){
            mSearch.setError("Please enter a company");
        }else {
            mCompany = mSearch.getText().toString().trim().replaceAll("\\s+", "+");
            if(mGoogleApiClient != null && mGoogleApiClient.isConnected()) {
                setLocationCheck();
            }else {
                enableLocation();
            }
        }
    }

    public void fetchData(){
        if(mCurrLocation != null){
            String lat = Double.toString(mCurrLocation.getLatitude());
            String lng = Double.toString(mCurrLocation.getLongitude());
            mPresenter.getBranches(mCompany, lat, lng);
        }else{
            showAlertDialog("Could not find your location");
        }
    }

    /** Initializes Google Api client if it hasn't been initialized. */
    private void setGoogleAPI(){
        if (mGoogleApiClient == null) {
            mGoogleApiClient = new GoogleApiClient.Builder(this)
                    .addConnectionCallbacks(this)
                    .addOnConnectionFailedListener(this)
                    .addApi(LocationServices.API)
                    .build();
        }
        mGoogleApiClient.connect();
    }

    /** Initialize location request if it hasn't been initialized. */
    private void createLocationRequest(){
        mLocationRequest = new LocationRequest();
        mLocationRequest.setInterval(10000);
        mLocationRequest.setFastestInterval(5000);
        mLocationRequest.setPriority(LocationRequest.PRIORITY_BALANCED_POWER_ACCURACY);
    }

    /**
     * Check if user has location services turned on and if it is then fetch data from server.
     * If location services is off, open pop-up that allows user to turn location
     * services on
     */
    private void setLocationCheck(){
        LocationSettingsRequest.Builder builder = new LocationSettingsRequest.Builder()
                .addLocationRequest(mLocationRequest)
                .setAlwaysShow(true);

        PendingResult<LocationSettingsResult> result = LocationServices.SettingsApi.checkLocationSettings(mGoogleApiClient, builder.build());
        result.setResultCallback(new ResultCallback<LocationSettingsResult>() {
            @Override
            public void onResult(LocationSettingsResult locationSettingsResult) {
                Status status = locationSettingsResult.getStatus();
                switch (status.getStatusCode()) {
                    case LocationSettingsStatusCodes.SUCCESS:
                        if (setCurrLocation()) {
                            fetchData();
                        } else {
                            startLocationUpdates();
                        }
                        break;
                    case LocationSettingsStatusCodes.RESOLUTION_REQUIRED:
                        try {
                            status.startResolutionForResult(
                                    MapsActivity.this,
                                    1000);
                        } catch (Exception e) {
                            // Ignore the error.
                        }
                        break;
                }
            }
        });
    }

    /**
     * Checks whether or not app should request for location permission or
     * initialize Google api if permission has been granted
     */
    private void enableLocation(){
        if(android.os.Build.VERSION.SDK_INT >= Build.VERSION_CODES.M){
            if(ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION)
                    == PackageManager.PERMISSION_GRANTED){
                setGoogleAPI();
            }else{
                checkLocationPermission();
            }
        }else{
            setGoogleAPI();
        }
    }

    /**
     * If location permission hasn't been granted, it will request the user for permission
     * to use their location
     */
    private void checkLocationPermission(){
        if(ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION)
                != PackageManager.PERMISSION_GRANTED){
            if(ActivityCompat.shouldShowRequestPermissionRationale(this,
                    Manifest.permission.ACCESS_FINE_LOCATION)){
                DialogFragment dialog = ConfirmDialog.newInstance("Location Permission",
                        "This app needs permission to access your location", ACTION_PERMISSION);
                dialog.show(getSupportFragmentManager(), "DIALOG");
            }else{
                ActivityCompat.requestPermissions(this, new String[]{Manifest.permission.ACCESS_FINE_LOCATION},
                        LOCATION_PERMISSION_REQUEST_CODE);
            }
        }
    }

    /**
     * Sets the last known location of the user
     * @return true if last known location is available, false if it is null
     */
    private boolean setCurrLocation(){
        try {
            mCurrLocation = LocationServices.FusedLocationApi.getLastLocation(mGoogleApiClient);
            return mCurrLocation != null;
        }catch (SecurityException e){
            return false;
        }
    }

    /**
     * Requests for location updates in cases where there is no known last location for the user
     */
    private void startLocationUpdates(){
        try {
            LocationServices.FusedLocationApi.requestLocationUpdates(mGoogleApiClient, mLocationRequest, this);
        }catch (SecurityException e){
            Log.e("Maps", e.toString());
            showAlertDialog("Could not track location");
        }
    }

    private void stopLocationUpdates(){
        if(mGoogleApiClient != null && mGoogleApiClient.isConnected())
            LocationServices.FusedLocationApi.removeLocationUpdates(mGoogleApiClient, this);
    }
}

