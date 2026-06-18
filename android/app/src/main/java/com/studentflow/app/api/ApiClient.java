package com.studentflow.app.api;

import android.content.Context;

import com.studentflow.app.Constants;
import com.studentflow.app.data.TokenStore;

import okhttp3.OkHttpClient;
import retrofit2.Retrofit;
import retrofit2.converter.gson.GsonConverterFactory;

public class ApiClient {
    private static Retrofit retrofit;

    public static ApiService service(Context context) {
        if (retrofit == null) {
            TokenStore tokenStore = new TokenStore(context);
            OkHttpClient client = new OkHttpClient.Builder()
                    .addInterceptor(chain -> {
                        okhttp3.Request.Builder builder = chain.request().newBuilder()
                                .header("Accept", "application/json")
                                .header("Content-Type", "application/json");
                        String token = tokenStore.getToken();
                        if (token != null && !token.trim().isEmpty()) {
                            builder.header("Authorization", "Bearer " + token);
                        }
                        return chain.proceed(builder.build());
                    })
                    .build();

            retrofit = new Retrofit.Builder()
                    .baseUrl(Constants.API_BASE_URL)
                    .client(client)
                    .addConverterFactory(GsonConverterFactory.create())
                    .build();
        }
        return retrofit.create(ApiService.class);
    }

    public static void reset() {
        retrofit = null;
    }
}
