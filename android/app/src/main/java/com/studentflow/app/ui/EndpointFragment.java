package com.studentflow.app.ui;

import android.os.Bundle;

import com.google.gson.JsonObject;
import com.studentflow.app.api.ApiClient;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class EndpointFragment extends BaseDataFragment {
    private static final String ARG_TITLE = "title";
    private static final String ARG_SUBTITLE = "subtitle";
    private static final String ARG_ENDPOINT = "endpoint";

    public static EndpointFragment newInstance(String title, String subtitle, String endpoint) {
        EndpointFragment fragment = new EndpointFragment();
        Bundle args = new Bundle();
        args.putString(ARG_TITLE, title);
        args.putString(ARG_SUBTITLE, subtitle);
        args.putString(ARG_ENDPOINT, endpoint);
        fragment.setArguments(args);
        return fragment;
    }

    @Override
    protected void configure() {
        String title = requireArguments().getString(ARG_TITLE);
        String subtitle = requireArguments().getString(ARG_SUBTITLE);
        setHeader(title, subtitle);
        addAction("Refresh", v -> load());
        load();
    }

    private void load() {
        setLoading(true);
        setStatus("", false);
        String endpoint = requireArguments().getString(ARG_ENDPOINT);
        Call<JsonObject> call;
        if ("classes".equals(endpoint)) {
            call = ApiClient.service(requireContext()).classes();
        } else if ("attendance".equals(endpoint)) {
            call = ApiClient.service(requireContext()).attendance(null, null);
        } else if ("assignments".equals(endpoint)) {
            call = ApiClient.service(requireContext()).assignments();
        } else if ("announcements".equals(endpoint)) {
            call = ApiClient.service(requireContext()).announcements();
        } else {
            showError("Unknown endpoint: " + endpoint);
            return;
        }
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                setLoading(false);
                if (response.isSuccessful()) {
                    renderData(response.body(), "No records found.");
                } else {
                    showError("Request failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                setLoading(false);
                showError("Network error: " + t.getMessage());
            }
        });
    }
}
