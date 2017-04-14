package adriantam18.crowdcontrol.Branch;

import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ListView;
import android.widget.ProgressBar;
import android.widget.TextView;

import java.util.ArrayList;
import java.util.List;

import adriantam18.crowdcontrol.Crowd.CrowdList;
import adriantam18.crowdcontrol.Model.BranchData;
import adriantam18.crowdcontrol.R;
import butterknife.BindView;
import butterknife.ButterKnife;

/**
 * This class is responsible for displaying a list of addresses and operating hours
 * of the branches of companies.
 *
 * It uses a listView with each row displaying the branch address listed on top and the operating hours right below.
 * Clicking on a branch will take the user to a new activity containing rooms for that branch.
 */
public class BranchList extends AppCompatActivity implements BranchView {

    /** ListView that will display the data contained in mListOfBranches. */
    @BindView(R.id.branch_list)
    ListView mListView;

    /** Adapter that bridges mListOfBranches and mListView. */
    private BranchListAdapter mBranchListAdapter;

    /** The company to which the branches belong. */
    private String mCompany;

    /** ProgressBar that displays while sending and waiting for request response. */
    @BindView(R.id.show_loading)
    ProgressBar mProgressBar;

    /** TextView for displaying error message. */
    @BindView(R.id.error_msg)
    TextView mErrorView;

    private BranchPresenter mPresenter;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_branch_list);
        ButterKnife.bind(this);

        mBranchListAdapter = new BranchListAdapter(this, new ArrayList<BranchData>());
        mListView.setAdapter(mBranchListAdapter);
        mPresenter = new BranchPresenter(this);

        /**Get the company for which to retrieve branches for. */
        Intent intent = getIntent();
        mCompany = intent.getStringExtra("company");
        setTitle(mCompany);

        mPresenter.getBranches(mCompany, null, null);

        setOnClickListener();
    }

    /**
     *  Retrieve BranchData of clicked item, and send company name, address
     *  and branch id to CrowdList activity to handle display of crowd reports
     */
    private void setOnClickListener(){
        mListView.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                mPresenter.getRooms(position);
            }
        });
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_branch_list, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        int id = item.getItemId();

        if (id == R.id.refresh) {
            mPresenter.getBranches(mCompany, null, null);
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
        mListView.setVisibility(View.GONE);
        mErrorView.setText(errorMsg);
        mErrorView.setVisibility(View.VISIBLE);
    }

    public void showBranches(List<BranchData> branches){
        mBranchListAdapter.replaceData(branches);
    }

    @Override
    public void showData(List<BranchData> branches){
        mBranchListAdapter.replaceData(branches);
    }

    @Override
    public void showRooms(BranchData branchData){
        Intent intent = new Intent(getBaseContext(), CrowdList.class);
        intent.putExtra("branch", branchData.getId());
        intent.putExtra("company", mCompany);
        intent.putExtra("address", branchData.getAddress());
        startActivity(intent);
    }
}
