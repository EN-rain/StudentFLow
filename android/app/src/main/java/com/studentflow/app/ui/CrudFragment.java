package com.studentflow.app.ui;

import android.os.Bundle;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import com.google.android.material.button.MaterialButton;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import com.studentflow.app.api.ApiClient;
import com.studentflow.app.data.TokenStore;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class CrudFragment extends BaseDataFragment {
    private static final String ARG_TYPE = "type";
    private String type;

    public static CrudFragment newInstance(String type) {
        CrudFragment fragment = new CrudFragment();
        Bundle args = new Bundle();
        args.putString(ARG_TYPE, type);
        fragment.setArguments(args);
        return fragment;
    }

    @Override
    protected void configure() {
        type = requireArguments().getString(ARG_TYPE);
        setHeader(title(), subtitle());
        addAction("Refresh", v -> load());
        addAction("Add", v -> openForm(null));
        if ("classes".equals(type) && isAdmin()) {
            addAction("Add Dummy", v -> submit(ApiClient.service(requireContext()).createDummyClass()));
        }
        load();
    }

    private void load() {
        statusView.setText("Loading...");
        listContainer.removeAllViews();
        listCall().enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (!response.isSuccessful()) {
                    showError(title() + " request failed: HTTP " + response.code());
                    return;
                }
                renderList(response.body());
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                showError("Network error: " + t.getMessage());
            }
        });
    }

    private void renderList(JsonObject body) {
        listContainer.removeAllViews();
        JsonElement data = body == null ? null : body.get("data");
        if (data == null || !data.isJsonArray()) {
            statusView.setText("Loaded.");
            addCard(body == null ? "No data returned." : summarize(body));
            return;
        }
        JsonArray rows = data.getAsJsonArray();
        statusView.setText(rows.size() + " records loaded.");
        if (rows.size() == 0) {
            addCard("No records found.");
            return;
        }
        for (JsonElement element : rows) {
            if (element.isJsonObject()) {
                renderCrudCard(element.getAsJsonObject());
            }
        }
    }

    private void renderCrudCard(JsonObject row) {
        addCard(summarize(row), v -> openForm(row));
        LinearLayout buttons = new LinearLayout(requireContext());
        buttons.setOrientation(LinearLayout.HORIZONTAL);
        MaterialButton edit = new MaterialButton(requireContext());
        edit.setText("Edit");
        edit.setOnClickListener(v -> openForm(row));
        MaterialButton delete = new MaterialButton(requireContext());
        delete.setText("Delete");
        delete.setOnClickListener(v -> {
            Integer id = intValue(row, "id");
            if (id != null) {
                confirm("Delete", "Delete this record?", () -> delete(id));
            }
        });
        buttons.addView(edit, new LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1));
        buttons.addView(delete, new LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1));
        listContainer.addView(buttons);
    }

    private void openForm(JsonObject existing) {
        FormDialog.show(requireContext(), (existing == null ? "Add " : "Edit ") + title(), fields(), existing, payload -> {
            Call<JsonObject> call;
            if (existing == null) {
                call = createCall(payload);
            } else {
                Integer id = intValue(existing, "id");
                if (id == null) {
                    showError("Cannot update record without id.");
                    return;
                }
                call = updateCall(id, payload);
            }
            submit(call);
        });
    }

    private void submit(Call<JsonObject> call) {
        statusView.setText("Saving...");
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    load();
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

    private void delete(int id) {
        statusView.setText("Deleting...");
        deleteCall(id).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    load();
                } else {
                    showError("Delete failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                showError("Network error: " + t.getMessage());
            }
        });
    }

    private Call<JsonObject> listCall() {
        if ("classes".equals(type)) {
            return ApiClient.service(requireContext()).classes();
        }
        if ("students".equals(type)) {
            return ApiClient.service(requireContext()).students(null, null);
        }
        if ("assignments".equals(type)) {
            return ApiClient.service(requireContext()).assignments();
        }
        return ApiClient.service(requireContext()).announcements();
    }

    private Call<JsonObject> createCall(JsonObject payload) {
        if ("classes".equals(type)) {
            return ApiClient.service(requireContext()).createClass(payload);
        }
        if ("students".equals(type)) {
            return ApiClient.service(requireContext()).createStudent(payload);
        }
        if ("assignments".equals(type)) {
            return ApiClient.service(requireContext()).createAssignment(payload);
        }
        return ApiClient.service(requireContext()).createAnnouncement(payload);
    }

    private Call<JsonObject> updateCall(int id, JsonObject payload) {
        if ("classes".equals(type)) {
            return ApiClient.service(requireContext()).updateClass(id, payload);
        }
        if ("students".equals(type)) {
            return ApiClient.service(requireContext()).updateStudent(id, payload);
        }
        if ("assignments".equals(type)) {
            return ApiClient.service(requireContext()).updateAssignment(id, payload);
        }
        return ApiClient.service(requireContext()).updateAnnouncement(id, payload);
    }

    private Call<JsonObject> deleteCall(int id) {
        if ("classes".equals(type)) {
            return ApiClient.service(requireContext()).deleteClass(id);
        }
        if ("students".equals(type)) {
            return ApiClient.service(requireContext()).deleteStudent(id);
        }
        if ("assignments".equals(type)) {
            return ApiClient.service(requireContext()).deleteAssignment(id);
        }
        return ApiClient.service(requireContext()).deleteAnnouncement(id);
    }

    private FormDialog.Field[] fields() {
        if ("classes".equals(type)) {
            return new FormDialog.Field[] {
                    FormDialog.text("class_name", "Class name", true),
                    FormDialog.text("section", "Section", false),
                    FormDialog.text("subject", "Subject", true),
                    FormDialog.text("grade_level", "Grade level", false),
                    FormDialog.text("school_year", "School year", false),
                    FormDialog.text("semester", "Semester", false),
                    FormDialog.text("schedule", "Schedule", false),
                    FormDialog.text("room", "Room", false),
                    FormDialog.number("teacher_id", "Teacher ID", true),
                    FormDialog.text("status", "Status: active or archived", false)
            };
        }
        if ("students".equals(type)) {
            return new FormDialog.Field[] {
                    FormDialog.text("student_number", "Student number", true),
                    FormDialog.text("first_name", "First name", true),
                    FormDialog.text("middle_name", "Middle name", false),
                    FormDialog.text("last_name", "Last name", true),
                    FormDialog.text("gender", "Gender: Male/Female/Other", false),
                    FormDialog.text("birth_date", "Birth date: YYYY-MM-DD", false),
                    FormDialog.text("email", "Email", true),
                    FormDialog.text("contact_number", "Contact number", false),
                    FormDialog.text("address", "Address", false),
                    FormDialog.text("guardian_name", "Guardian name", false),
                    FormDialog.text("guardian_contact", "Guardian contact", false),
                    FormDialog.text("status", "Status: active or disabled", false)
            };
        }
        if ("assignments".equals(type)) {
            return new FormDialog.Field[] {
                    FormDialog.number("class_id", "Class ID", true),
                    FormDialog.text("title", "Title", true),
                    FormDialog.text("description", "Description", false),
                    FormDialog.text("date_assigned", "Date assigned: YYYY-MM-DD", true),
                    FormDialog.text("deadline", "Deadline: YYYY-MM-DD", true),
                    FormDialog.number("maximum_score", "Maximum score", true),
                    FormDialog.text("status", "Status", false),
                    FormDialog.text("attachment_link", "Attachment URL", false)
            };
        }
        return new FormDialog.Field[] {
                FormDialog.number("class_id", "Class ID, blank for all", false),
                FormDialog.text("title", "Title", true),
                FormDialog.text("message", "Message", true),
                FormDialog.text("priority", "Priority: Normal/Important/Urgent", false),
                FormDialog.text("publish_date", "Publish date: YYYY-MM-DD", true),
                FormDialog.text("expiration_date", "Expiration date: YYYY-MM-DD", false)
        };
    }

    private boolean isAdmin() {
        try {
            String json = new TokenStore(requireContext()).getUserJson();
            return json != null
                    && JsonParser.parseString(json).getAsJsonObject().has("role")
                    && "admin".equals(JsonParser.parseString(json).getAsJsonObject().get("role").getAsString());
        } catch (RuntimeException e) {
            return false;
        }
    }

    private String title() {
        if ("classes".equals(type)) {
            return "Classes";
        }
        if ("students".equals(type)) {
            return "Students";
        }
        if ("assignments".equals(type)) {
            return "Assignments";
        }
        return "Announcements";
    }

    private String subtitle() {
        if ("classes".equals(type)) {
            return "Create, edit, archive, or delete teacher-owned classes.";
        }
        if ("students".equals(type)) {
            return "Create, edit, disable, or delete students visible to your classes.";
        }
        if ("assignments".equals(type)) {
            return "Create, edit, or delete assignments for your classes.";
        }
        return "Create, edit, or delete class announcements.";
    }
}
