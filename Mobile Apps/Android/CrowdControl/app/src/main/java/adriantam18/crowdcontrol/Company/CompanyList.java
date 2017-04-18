package adriantam18.crowdcontrol.Company;

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

import adriantam18.crowdcontrol.Branch.BranchList;
import adriantam18.crowdcontrol.Model.CompanyData;
import adriantam18.crowdcontrol.R;
import butterknife.BindView;
import butterknife.ButterKnife;

/**
 * This activity will display all the available companies that are in the remote
 * database. Clicking on a company will take the user to another activity that will
 * display available branches for that company.
 */
public class CompanyList extends AppCompatActivity implements CompanyView {

    /** Adapter that bridges mCompanies and mListView. */
    private CompanyListAdapter mAdapter;

    /** listView responsible for displaying data. */
    @BindView(R.id.company_list)
    ListView mListView;

    /** ProgressBar that displays while sending and waiting for request response. */
    @BindView(R.id.show_loading)
    ProgressBar mProgressBar;

    /** TextView for displaying error message. */
    @BindView(R.id.error_msg)
    TextView mErrorView;

    private CompanyPresenter mPresenter;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_company_list);
        ButterKnife.bind(this);

        setTitle("Companies");

        mAdapter = new CompanyListAdapter(this, new ArrayList<CompanyData>());
        mListView.setAdapter(mAdapter);

        mPresenter = new CompanyPresenter(this);
        mPresenter.getCompanies();
        setOnClickListener();
    }

    /**
     * Get the company name and send it to BranchList activity so it knows which company to get
     * branches from
     */
    private void setOnClickListener(){
        mListView.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                mPresenter.showBranches(position);
            }
        });
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_company_list, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

        //noinspection SimplifiableIfStatement
        if (id == R.id.refresh) {
            mPresenter.getCompanies();
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

    @Override
    public void showData(List<CompanyData> companies){
        mErrorView.setVisibility(View.GONE);
        mListView.setVisibility(View.VISIBLE);

        if(!companies.isEmpty()) {
            mAdapter.replaceData(companies);
        }else{
            showError("No companies found");
        }
    }

    @Override
    public void showBranches(String companyName){
        Intent intent = new Intent(getBaseContext(), BranchList.class);
        intent.putExtra("company", companyName);
        startActivity(intent);
    }
}