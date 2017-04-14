package adriantam18.crowdcontrol.Company;

import java.util.HashMap;
import java.util.LinkedList;
import java.util.List;

import adriantam18.crowdcontrol.CrowdControlAPI.CompanyResponse;
import adriantam18.crowdcontrol.CrowdControlAPI.CompanyService;
import adriantam18.crowdcontrol.CrowdControlAPI.ServiceGenerator;
import adriantam18.crowdcontrol.Model.CompanyData;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class CompanyPresenter {
    private CompanyView mView;
    private List<CompanyData> mCompanies;

    public CompanyPresenter(CompanyView view){
        mView = view;
        mCompanies = new LinkedList<>();
    }

    public void getCompanies(){
        CompanyService service = ServiceGenerator.createClass(CompanyService.class);
        Call<CompanyResponse> call = service.getCompanies(new HashMap<String, String>());
        call.enqueue(new Callback<CompanyResponse>() {
            @Override
            public void onResponse(Call<CompanyResponse> call, Response<CompanyResponse> response) {
                if(response.isSuccessful()){
                    mCompanies = response.body().getData();
                    mView.showData(mCompanies);
                }else{
                    mView.showError(response.message());
                }
            }

            @Override
            public void onFailure(Call<CompanyResponse> call, Throwable t) {
                mView.showError("Oops. Something has gone wrong, Please try again.");
            }
        });
    }

    public void showBranches(int position){
        if(!mCompanies.isEmpty()){
            mView.showBranches(mCompanies.get(position).getName());
        }
    }
}
