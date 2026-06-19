package com.studentflow.app.api;

import android.content.Context;

import com.studentflow.app.Constants;
import com.studentflow.app.data.TokenStore;

import java.io.File;
import java.util.concurrent.TimeUnit;

import okhttp3.Cache;
import okhttp3.OkHttpClient;
import retrofit2.Retrofit;
import retrofit2.converter.gson.GsonConverterFactory;

public class ApiClient {
    private static Retrofit retrofit;

    public static ApiService service(Context context) {
        if (retrofit == null) {
            Context appContext = context.getApplicationContext();
            TokenStore tokenStore = new TokenStore(appContext);
            Cache cache = new Cache(new File(appContext.getCacheDir(), "api-cache"), 10L * 1024L * 1024L);
            OkHttpClient client = new OkHttpClient.Builder()
                    .cache(cache)
                    .connectTimeout(10, TimeUnit.SECONDS)
                    .readTimeout(20, TimeUnit.SECONDS)
                    .writeTimeout(20, TimeUnit.SECONDS)
                    .addInterceptor(chain -> {
                        okhttp3.Request.Builder builder = chain.request().newBuilder()
                                .header("Accept", "application/json");
                        String token = tokenStore.getToken();
                        if (token != null && !token.trim().isEmpty()) {
                            builder.header("Authorization", "Bearer " + token);
                        }
                        return chain.proceed(builder.build());
                    })
                    .addNetworkInterceptor(chain -> {
                        okhttp3.Response response = chain.proceed(chain.request());
                        if ("GET".equalsIgnoreCase(chain.request().method())
                                && response.header("Cache-Control") == null) {
                            return response.newBuilder()
                                    .header("Cache-Control", "private, max-age=30")
                                    .build();
                        }
                        return response;
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
