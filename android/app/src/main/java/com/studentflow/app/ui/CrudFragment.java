package com.studentflow.app.ui;

import android.os.Bundle;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.ScrollView;
import android.widget.TextView;

import androidx.appcompat.app.AlertDialog;

import com.google.android.material.button.MaterialButton;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.studentflow.app.api.ApiClient;

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
        load();
    }

    private void load() {
        setLoading(true);
        setStatus("", false);
        listContainer.removeAllViews();
        listCall().enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                setLoading(false);
                if (!response.isSuccessful()) {
                    showError(title() + " request failed: HTTP " + response.code());
                    return;
                }
                renderList(response.body());
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                setLoading(false);
                showError("Network error: " + t.getMessage());
            }
        });
    }

    private void renderList(JsonObject body) {
        listContainer.removeAllViews();
        JsonElement data = body == null ? null : body.get("data");
        if (data == null || !data.isJsonArray()) {
            setStatus("Loaded.", false);
            addCard(body == null ? "No data returned." : summarize(body));
            return;
        }
        JsonArray rows = data.getAsJsonArray();
        setStatus(rows.size() + " records loaded.", false);
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
        if ("classes".equals(type)) {
            MaterialButton requests = new MaterialButton(requireContext());
            requests.setText("Requests");
            requests.setOnClickListener(v -> {
                Integer id = intValue(row, "id");
                if (id != null) {
                    openJoinRequests(id);
                }
            });
            buttons.addView(requests, new LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1));
        }
        buttons.addView(delete, new LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1));
        listContainer.addView(buttons);
    }

    private void openJoinRequests(int classId) {
        setLoading(true);
        setStatus("Loading join requests...", false);
        ApiClient.service(requireContext()).classJoinRequests(classId).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (!isAdded() || getView() == null) {
                    return;
                }
                setLoading(false);
                if (!response.isSuccessful() || response.body() == null) {
                    setStatus("Join requests failed: HTTP " + response.code(), true);
                    return;
                }

                JsonArray rows = response.body().getAsJsonArray("data");
                LinearLayout content = new LinearLayout(requireContext());
                content.setOrientation(LinearLayout.VERTICAL);
                int padding = (int) (16 * getResources().getDisplayMetrics().density);
                content.setPadding(padding, padding, padding, padding);

                if (rows == null || rows.size() == 0) {
                    TextView empty = new TextView(requireContext());
                    empty.setText("No join requests.");
                    content.addView(empty);
                } else {
                    for (JsonElement element : rows) {
                        if (!element.isJsonObject()) {
                            continue;
                        }
                        JsonObject request = element.getAsJsonObject();
                        JsonObject student = request.has("student") && request.get("student").isJsonObject()
                                ? request.getAsJsonObject("student")
                                : new JsonObject();
                        LinearLayout row = new LinearLayout(requireContext());
                        row.setOrientation(LinearLayout.VERTICAL);
                        TextView label = new TextView(requireContext());
                        label.setText(stringValue(student, "first_name") + " " + stringValue(student, "last_name")
                                + "\n" + stringValue(student, "student_number")
                                + "\nStatus: " + stringValue(request, "status"));
                        row.addView(label);

                        if ("pending".equals(stringValue(request, "status"))) {
                            LinearLayout actions = new LinearLayout(requireContext());
                            actions.setOrientation(LinearLayout.HORIZONTAL);
                            MaterialButton approve = new MaterialButton(requireContext());
                            approve.setText("Approve");
                            MaterialButton reject = new MaterialButton(requireContext());
                            reject.setText("Reject");
                            Integer requestId = intValue(request, "id");
                            approve.setOnClickListener(v -> reviewJoinRequest(requestId, "approved", classId, approve, reject));
                            reject.setOnClickListener(v -> reviewJoinRequest(requestId, "rejected", classId, approve, reject));
                            actions.addView(approve);
                            actions.addView(reject);
                            row.addView(actions);
                        }
                        content.addView(row);
                    }
                }

                ScrollView scroll = new ScrollView(requireContext());
                scroll.addView(content);
                new AlertDialog.Builder(requireContext())
                        .setTitle("Classroom Join Requests")
                        .setView(scroll)
                        .setPositiveButton("Close", null)
                        .show();
                setStatus("Join requests loaded.", false);
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                if (isAdded() && getView() != null) {
                    setLoading(false);
                    setStatus("Network error: " + t.getMessage(), true);
                }
            }
        });
    }

    private void reviewJoinRequest(Integer requestId, String decision, int classId, MaterialButton approveButton, MaterialButton rejectButton) {
        if (requestId == null) {
            return;
        }
        approveButton.setEnabled(false);
        rejectButton.setEnabled(false);
        setLoading(true);
        setStatus("Reviewing join request...", false);
        JsonObject payload = new JsonObject();
        payload.addProperty("decision", decision);
        ApiClient.service(requireContext()).reviewClassJoinRequest(requestId, payload).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (!isAdded() || getView() == null) {
                    return;
                }
                setLoading(false);
                if (response.isSuccessful()) {
                    setStatus("Join request " + decision + ".", false);
                    load();
                } else {
                    approveButton.setEnabled(true);
                    rejectButton.setEnabled(true);
                    setStatus("Review failed: HTTP " + response.code(), true);
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                if (isAdded() && getView() != null) {
                    setLoading(false);
                    approveButton.setEnabled(true);
                    rejectButton.setEnabled(true);
                    setStatus("Network error: " + t.getMessage(), true);
                }
            }
        });
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
        setLoading(true);
        setStatus("Saving...", false);
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                setLoading(false);
                if (response.isSuccessful()) {
                    load();
                } else {
                    showError("Save failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                setLoading(false);
                showError("Network error: " + t.getMessage());
            }
        });
    }

    private void delete(int id) {
        setLoading(true);
        setStatus("Deleting...", false);
        deleteCall(id).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                setLoading(false);
                if (response.isSuccessful()) {
                    load();
                } else {
                    showError("Delete failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                setLoading(false);
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
