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
    private static ApiService apiService;
    private static OkHttpClient httpClient;
    private static Cache httpCache;

    public static synchronized ApiService service(Context context) {
        if (retrofit == null) {
            Context appContext = context.getApplicationContext();
            TokenStore tokenStore = new TokenStore(appContext);
            httpCache = new Cache(new File(appContext.getCacheDir(), "api-cache"), 10L * 1024L * 1024L);
            httpClient = new OkHttpClient.Builder()
                    .cache(httpCache)
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
                    .build();

            retrofit = new Retrofit.Builder()
                    .baseUrl(Constants.API_BASE_URL)
                    .client(httpClient)
                    .addConverterFactory(GsonConverterFactory.create())
                    .build();
            apiService = retrofit.create(ApiService.class);
        }
        return apiService;
    }

    public static synchronized void reset() {
        if (httpClient != null) {
            httpClient.dispatcher().cancelAll();
            httpClient.connectionPool().evictAll();
        }
        if (httpCache != null) {
            try {
                httpCache.evictAll();
            } catch (java.io.IOException ignored) {
                // Cache cleanup should never block sign-out or account changes.
            }
        }
        retrofit = null;
        apiService = null;
        httpClient = null;
        httpCache = null;
    }
}
