package adriantam18.crowdcontrol.CrowdControlAPI;

import java.util.Map;

import retrofit2.Call;
import retrofit2.http.GET;
import retrofit2.http.Path;
import retrofit2.http.QueryMap;

public interface CrowdService {
    @GET("/api/v1/rooms")
    Call<CrowdResponse> getRooms(@QueryMap(encoded = true) Map<String, String> params);

    @GET("/api/v1/rooms/{id}")
    Call<CrowdResponse> getRoomWithId(@Path("id") String id);
}
