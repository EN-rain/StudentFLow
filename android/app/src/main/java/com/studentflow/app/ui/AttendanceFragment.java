package com.studentflow.app.ui;

import com.google.gson.JsonArray;
import com.google.gson.JsonObject;
import com.studentflow.app.api.ApiClient;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class AttendanceFragment extends BaseDataFragment {
    public static AttendanceFragment newInstance() {
        return new AttendanceFragment();
    }

    @Override
    protected void configure() {
        setHeader("Attendance", "Filter attendance, mark a class present, or save a single student status.");
        addAction("Refresh", v -> load(null, null));
        addAction("Filter", v -> filter());
        addAction("Mark All", v -> markAllPresent());
        addAction("Save One", v -> saveOne());
        load(null, null);
    }

    private void filter() {
        FormDialog.show(requireContext(), "Filter Attendance", new FormDialog.Field[] {
                FormDialog.number("class_id", "Class ID", false),
                FormDialog.text("date", "Date: YYYY-MM-DD", false)
        }, null, payload -> load(
                payload.has("class_id") ? payload.get("class_id").getAsInt() : null,
                payload.has("date") ? payload.get("date").getAsString() : null
        ));
    }

    private void load(Integer classId, String date) {
        statusView.setText("Loading attendance...");
        ApiClient.service(requireContext()).attendance(classId, date).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    renderData(response.body(), "No attendance records found.");
                } else {
                    showError("Attendance request failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                showError("Network error: " + t.getMessage());
            }
        });
    }

    private void markAllPresent() {
        FormDialog.show(requireContext(), "Mark All Present", new FormDialog.Field[] {
                FormDialog.number("class_id", "Class ID", true),
                FormDialog.text("attendance_date", "Date: YYYY-MM-DD", true),
                FormDialog.text("remarks", "Remarks", false)
        }, null, payload -> submit(ApiClient.service(requireContext()).markAllPresent(payload)));
    }

    private void saveOne() {
        FormDialog.show(requireContext(), "Save Attendance Record", new FormDialog.Field[] {
                FormDialog.number("class_id", "Class ID", true),
                FormDialog.text("attendance_date", "Date: YYYY-MM-DD", true),
                FormDialog.number("student_id", "Student ID", true),
                FormDialog.text("status", "Present/Absent/Late/Excused", true),
                FormDialog.text("remarks", "Remarks", false)
        }, null, payload -> {
            JsonObject record = new JsonObject();
            record.addProperty("student_id", payload.get("student_id").getAsInt());
            record.addProperty("status", payload.get("status").getAsString());
            if (payload.has("remarks")) {
                record.addProperty("remarks", payload.get("remarks").getAsString());
            }
            JsonArray records = new JsonArray();
            records.add(record);
            JsonObject request = new JsonObject();
            request.addProperty("class_id", payload.get("class_id").getAsInt());
            request.addProperty("attendance_date", payload.get("attendance_date").getAsString());
            request.add("records", records);
            submit(ApiClient.service(requireContext()).saveAttendance(request));
        });
    }

    private void submit(Call<JsonObject> call) {
        statusView.setText("Saving...");
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    renderData(response.body(), "Saved.");
                } else {
                    showError("Save failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                showError("Network error: " + t.getMessage());
            }
        });
    }
}
