package adriantam18.crowdcontrol.BranchMaps;

import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.Color;
import android.location.Location;
import android.os.Bundle;
import android.support.v4.app.FragmentActivity;
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
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import adriantam18.crowdcontrol.Model.BranchData;
import adriantam18.crowdcontrol.Branch.BranchPresenter;
import adriantam18.crowdcontrol.Branch.BranchView;
import adriantam18.crowdcontrol.Crowd.CrowdList;
import adriantam18.crowdcontrol.R;
import butterknife.BindView;
import butterknife.ButterKnife;

public class MapsActivity extends FragmentActivity implements
        OnMapReadyCallback,
        GoogleApiClient.ConnectionCallbacks,
        GoogleApiClient.OnConnectionFailedListener,
        LocationListener,
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

    /** Alert dialog used to display messages. */
    private AlertDialog mAlertDialog;

    /** Displays while app is fetching data. */
    private AlertDialog mWaitDialog;

    /** Company that the user is interested in getting locations for. */
    private String mCompany;

    /** Used in conjunction with the FusedLocationProviderApi. */
    private LocationRequest mLocationRequest;

    private Map<String, Boolean> mMarkers;

    @BindView(R.id.enter_comp)
    EditText mSearch;

    private BranchPresenter mPresenter;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_maps);
        ButterKnife.bind(this);

        createLocationRequest();
        createAlertDialog();

        mMarkers = new HashMap<>();
        mMapsAdapter = new MapsListAdapter(this, new ArrayList<BranchData>());
        mListView.setAdapter(mMapsAdapter);
        setListViewListenter();

        Intent intent = getIntent();
        mCompany = intent.getStringExtra("company");
        mSearch.setText(mCompany.trim());
        mSearch.setSelection(mCompany.length() - 1);
        mSearch.clearFocus();

        mPresenter = new BranchPresenter(this);

        SupportMapFragment mapFragment = (SupportMapFragment) getSupportFragmentManager()
                .findFragmentById(R.id.map);
        mapFragment.getMapAsync(this);
    }


    @Override
    protected void onResume() {
        super.onResume();
        setGoogleAPI();
    }

    @Override
    protected void onPause(){
        super.onPause();
        stopLocationUpdates();
    }

    @Override
    protected void onDestroy(){
        super.onDestroy();
    }

    @Override
    protected void onStop(){
        stopLocationUpdates();
        mGoogleApiClient.disconnect();
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
        //Try to connect to google API
        setGoogleAPI();
    }

    @Override
    public void onLocationChanged(Location location){
        mCurrLocation = location;
        fetchData();
        stopLocationUpdates();
    }

    @Override
    public void onConnected(Bundle connectionHunt){
        setLocationCheck();
    }

    @Override
    public void onConnectionFailed(ConnectionResult result){
        Log.e("connection failed", result.toString());
    }

    @Override
    public void onConnectionSuspended(int result){
        Log.e("connection suspended", Integer.toString(result));
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data){
        //If the user turned on location services, retrieve data from server
        if(resultCode == RESULT_OK){
            if(setCurrLocation())
                fetchData();
            else
                startLocationUpdates();
        }else{
            showAlertDialog("To use this feature, the app needs access to your location.");
        }
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
     * Initializes the alert dialog that this activity uses to display messages
     */
    private void createAlertDialog(){
        mAlertDialog = new AlertDialog.Builder(this).create();
        mAlertDialog.setButton(DialogInterface.BUTTON_NEUTRAL, "Okay", new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                switch (which) {
                    case DialogInterface.BUTTON_NEUTRAL:
                        break;
                }
            }
        });
    }

    /**
     * Opens up an alert dialog to display a message
     * @param message the message to display
     */
    private void showAlertDialog(String message){
        mAlertDialog.setMessage(message);
        mAlertDialog.show();
    }

    public void mapSearchClicked(View view){
        if(mSearch.getText().toString().isEmpty()){
            mSearch.setError("Please enter a company");
        }else {
            mCompany = mSearch.getText().toString().trim().replaceAll("\\s+", "+");
            setLocationCheck();
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
        mLocationRequest.setPriority(LocationRequest.PRIORITY_HIGH_ACCURACY);
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

            int index = 1;
            for (BranchData branchData : closeBranches) {
                LatLng latLng = new LatLng(Double.parseDouble(branchData.getLat()), Double.parseDouble(branchData.getLng()));
                setMapMarker(latLng, branchData.getAddress(), Color.RED, index++);
            }
        }
    }

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

    private boolean setCurrLocation(){
        try {
            mCurrLocation = LocationServices.FusedLocationApi.getLastLocation(mGoogleApiClient);
            return mCurrLocation != null;
        }catch (SecurityException e){
            return false;
        }
    }

    private void startLocationUpdates(){
        try {
            LocationServices.FusedLocationApi.requestLocationUpdates(mGoogleApiClient, mLocationRequest, this);
        }catch (SecurityException e){
            Log.e("Maps", e.toString());
            showAlertDialog("Could not track location");
        }
    }

    private void stopLocationUpdates(){
        LocationServices.FusedLocationApi.removeLocationUpdates(mGoogleApiClient, this);
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
}

