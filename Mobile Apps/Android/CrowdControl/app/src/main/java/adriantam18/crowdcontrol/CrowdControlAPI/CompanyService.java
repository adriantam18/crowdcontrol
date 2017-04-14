package adriantam18.crowdcontrol.CrowdControlAPI;

import java.util.Map;

import retrofit2.Call;
import retrofit2.http.GET;
import retrofit2.http.Path;
import retrofit2.http.QueryMap;

public interface CompanyService {
    @GET("/api/v1/companies")
    Call<CompanyResponse> getCompanies(@QueryMap(encoded = true) Map<String, String> params);

    @GET("/api/v1/companies/{id}")
    Call<CompanyResponse> getCompanyWithId(@Path("id") String id);
}
