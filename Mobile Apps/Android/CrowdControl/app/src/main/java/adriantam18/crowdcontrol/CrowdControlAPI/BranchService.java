package adriantam18.crowdcontrol.CrowdControlAPI;

import java.util.Map;

import retrofit2.Call;
import retrofit2.http.GET;
import retrofit2.http.Path;
import retrofit2.http.QueryMap;

public interface BranchService {
    @GET("/api/v1/branches")
    Call<BranchResponse> getBranches(@QueryMap(encoded = true) Map<String, String> params);

    @GET("/api/v1/branches/{id}")
    Call<BranchResponse> getBranchWithId(@Path("id") String id);
}
