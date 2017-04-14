package adriantam18.crowdcontrol.Crowd;

import java.util.HashMap;
import java.util.LinkedList;
import java.util.List;
import java.util.Map;

import adriantam18.crowdcontrol.CrowdControlAPI.CrowdResponse;
import adriantam18.crowdcontrol.CrowdControlAPI.CrowdService;
import adriantam18.crowdcontrol.CrowdControlAPI.ServiceGenerator;
import adriantam18.crowdcontrol.Model.CrowdData;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class CrowdPresenter {
    private List<CrowdData> crowdData;
    private CrowdView mView;

    public CrowdPresenter(CrowdView view){
        mView = view;
        crowdData = new LinkedList<>();
    }

    public void getRooms(String branchId){
        Map<String, String> params = new HashMap<>();
        params.put("branch_id", branchId);

        CrowdService service = ServiceGenerator.createClass(CrowdService.class);
        Call<CrowdResponse> call = service.getRooms(params);
        call.enqueue(new Callback<CrowdResponse>() {
            @Override
            public void onResponse(Call<CrowdResponse> call, Response<CrowdResponse> response) {
                if(response.isSuccessful()){
                    crowdData = response.body().getData();
                    mView.showData(crowdData);
                }else{
                    mView.showError(response.message());
                }
            }

            @Override
            public void onFailure(Call<CrowdResponse> call, Throwable t) {
                mView.showError("Oops. Something has gone wrong, Please try again.");
            }
        });
    }
}
