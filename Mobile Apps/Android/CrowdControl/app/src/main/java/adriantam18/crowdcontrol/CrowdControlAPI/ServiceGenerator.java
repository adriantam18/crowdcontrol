package adriantam18.crowdcontrol.CrowdControlAPI;

import com.google.gson.Gson;
import com.google.gson.GsonBuilder;

import retrofit2.Retrofit;
import retrofit2.converter.gson.GsonConverterFactory;

/**
 * Helps create our client classes.
 * Taken from https://futurestud.io/tutorials/retrofit-getting-started-and-android-client
 */
public class ServiceGenerator {
    public static final String API_BASE_URL = "YOUR BASE URL";
    private static Gson gson = new GsonBuilder().create();
    private static Retrofit retrofit = new Retrofit.Builder()
            .baseUrl(API_BASE_URL)
            .addConverterFactory(GsonConverterFactory.create(gson))
            .build();

    public static <S> S createClass(Class<S> service){
        return retrofit.create(service);
    }
}
