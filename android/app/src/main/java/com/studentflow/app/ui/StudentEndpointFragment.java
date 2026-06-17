package com.studentflow.app.ui;

import android.os.Bundle;

import com.google.gson.JsonObject;
import com.studentflow.app.api.ApiClient;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class StudentEndpointFragment extends BaseDataFragment {
    private static final String ARG_TITLE = "title";
    private static final String ARG_SUBTITLE = "subtitle";
    private static final String ARG_ENDPOINT = "endpoint";

    public static StudentEndpointFragment newInstance(String title, String subtitle, String endpoint) {
        StudentEndpointFragment fragment = new StudentEndpointFragment();
        Bundle args = new Bundle();
        args.putString(ARG_TITLE, title);
        args.putString(ARG_SUBTITLE, subtitle);
        args.putString(ARG_ENDPOINT, endpoint);
        fragment.setArguments(args);
        return fragment;
    }

    @Override
    protected void configure() {
        setHeader(requireArguments().getString(ARG_TITLE), requireArguments().getString(ARG_SUBTITLE));
        addAction("Refresh", v -> load());
        load();
    }

    private void load() {
        statusView.setText("Loading...");
        String endpoint = requireArguments().getString(ARG_ENDPOINT);
        Call<JsonObject> call;
        if ("dashboard".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentDashboard();
        } else if ("profile".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentProfile();
        } else if ("classes".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentClasses();
        } else if ("announcements".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentAnnouncements();
        } else if ("assignments".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentAssignments();
        } else if ("grades".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentGrades();
        } else if ("attendance".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentAttendance();
        } else {
            showError("Unknown student endpoint.");
            return;
        }
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    renderData(response.body(), "No student records found.");
                } else {
                    showError("Request failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                showError("Network error: " + t.getMessage());
            }
        });
    }
}
