package com.studentflow.app.ui;

import android.content.Intent;

import com.google.gson.JsonObject;
import com.studentflow.app.api.ApiClient;
import com.studentflow.app.data.TokenStore;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class ProfileFragment extends BaseDataFragment {
    public static ProfileFragment newInstance() {
        return new ProfileFragment();
    }

    @Override
    protected void configure() {
        setHeader("Profile", "Authenticated user and linked teacher profile.");
        addAction("Refresh", v -> load());
        addAction("Password", v -> startActivity(new Intent(requireContext(), ChangePasswordActivity.class)));
        String cached = new TokenStore(requireContext()).getUserJson();
        if (cached != null) {
            addCard("Cached user\n" + cached);
        }
        load();
    }

    private void load() {
        statusView.setText("Loading profile...");
        ApiClient.service(requireContext()).me().enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    renderData(response.body(), "No profile returned.");
                } else {
                    showError("Profile request failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                showError("Network error: " + t.getMessage());
            }
        });
    }
}
