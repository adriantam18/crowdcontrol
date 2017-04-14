package adriantam18.crowdcontrol;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;

import adriantam18.crowdcontrol.Branch.BranchList;
import adriantam18.crowdcontrol.BranchMaps.MapsActivity;
import adriantam18.crowdcontrol.Company.CompanyList;

/**
 *  This activity will allow the user to search for a specific company
 *  or view all companies.
 */
public class MainActivity extends AppCompatActivity {
    private EditText companyToSearch;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        companyToSearch = (EditText) findViewById(R.id.enter_company);
    }

    public void searchBranchesClicked(View view){
        String company = companyToSearch.getText().toString();

        //Check if the user entered a company before launching the activity that displays branches
        if(!company.isEmpty()){
            Intent intent = new Intent(this, BranchList.class);
            intent.putExtra("company", company);
            startActivity(intent);
        }else{
            companyToSearch.setError("Please enter a company to search");
        }
    }

    public void nearMeClicked(View view){
        String company = companyToSearch.getText().toString();

        //Check if the user entered a company before launching the activity that displays branches
        if(!company.isEmpty()){
            Intent intent = new Intent(this, MapsActivity.class);
            intent.putExtra("company", company);
            startActivity(intent);
        }else{
            companyToSearch.setError("Please enter a company to search");
        }
    }

    public void viewCompaniesClicked(View view){
        Intent intent = new Intent(this, CompanyList.class);
        startActivity(intent);
    }
}
