package adriantam18.crowdcontrol.Branch;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

import adriantam18.crowdcontrol.CrowdControlAPI.BranchResponse;
import adriantam18.crowdcontrol.CrowdControlAPI.BranchService;
import adriantam18.crowdcontrol.CrowdControlAPI.ServiceGenerator;
import adriantam18.crowdcontrol.Model.BranchData;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class BranchPresenter {
    private BranchView view;
    private List<BranchData> branches;
    
    public BranchPresenter(BranchView view){
        this.view = view;
    }
    
    public void getBranches(String companyName, String lat, String lng){
        companyName = companyName.trim().replaceAll("\\s+", "+");

        Map<String, String> params = new HashMap<>();
        params.put("company", companyName);

        if(lat != null && lng != null){
            String latlng = lat + "," + lng;
            params.put("closeto", latlng);
        }

        BranchService service = ServiceGenerator.createClass(BranchService.class);
        Call<BranchResponse> call = service.getBranches(params);
        call.enqueue(new Callback<BranchResponse>() {
            @Override
            public void onResponse(Call<BranchResponse> call, Response<BranchResponse> response) {
                if(response.isSuccessful()){
                    branches = response.body().getData();
                    view.showData(branches);
                }else{
                    view.showError(response.message());
                }
            }

            @Override
            public void onFailure(Call<BranchResponse> call, Throwable t) {
                view.showError("Oops. Something has gone wrong, Please try again.");
            }
        });
    }

    public void getRooms(int position){
        view.showRooms(branches.get(position));
    }
}
