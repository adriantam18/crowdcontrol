package adriantam18.crowdcontrol.Crowd;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.ExpandableListView;
import android.widget.ProgressBar;
import android.widget.TextView;

import java.util.HashMap;
import java.util.LinkedList;
import java.util.List;

import adriantam18.crowdcontrol.Model.CrowdData;
import adriantam18.crowdcontrol.R;
import butterknife.BindView;
import butterknife.ButterKnife;

/**
 * This class will display crowd information using an expandable list view.
 * The room numbers will serve as the headers and the crowd information for
 * that room will serve as the children.
 */
public class CrowdList extends AppCompatActivity implements CrowdView {

    /** Expandable list adapter for the expandable list view of this activity. */
    private CustomExpListAdapter mCustomExpListAdapter;

    /** Expandable list view for displaying information about a list of CrowdData. */
    @BindView(R.id.branches_exp_list)
    ExpandableListView mExpandableListView;

    /** id of the branch to get crowd information from. */
    private String mBranchId;

    /** ProgressBar that displays while sending and waiting for request response. */
    @BindView(R.id.show_loading)
    ProgressBar mProgressBar;

    /** TextView for displaying error message. */
    @BindView(R.id.room_error_msg)
    TextView mErrorView;

    private CrowdPresenter mPresenter;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_crowd_list);
        ButterKnife.bind(this);

        mCustomExpListAdapter = new CustomExpListAdapter(this, new LinkedList<String>(), new HashMap<String, List<String>>());
        mExpandableListView.setAdapter(mCustomExpListAdapter);

        Intent intent = getIntent();
        setTitle(intent.getStringExtra("company") + " " + intent.getStringExtra("address"));

        mBranchId = intent.getStringExtra("branch");

        mPresenter = new CrowdPresenter(this);
        mPresenter.getRooms(mBranchId);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_crowd_list, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        int id = item.getItemId();

        if (id == R.id.refresh) {
            mPresenter.getRooms(mBranchId);
        }

        return super.onOptionsItemSelected(item);
    }

    /**
     * Displays an error message in the middle of the screen by making the
     * text view for displaying error visible
     * @param errorMsg the error message to display
     */
    @Override
    public void showError(String errorMsg){
        mExpandableListView.setVisibility(View.GONE);
        mErrorView.setText(errorMsg);
        mErrorView.setVisibility(View.VISIBLE);
    }

    @Override
    public void showData(List<CrowdData> crowdData){
        mErrorView.setVisibility(View.GONE);
        mExpandableListView.setVisibility(View.VISIBLE);

        if(!crowdData.isEmpty()) {
            mCustomExpListAdapter.replaceData(crowdData);
        }else{
            showError("No rooms found");
        }
    }
}
