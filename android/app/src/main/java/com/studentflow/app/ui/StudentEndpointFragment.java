package com.studentflow.app.ui;

import android.os.Bundle;

import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.studentflow.app.R;
import com.studentflow.app.api.ApiClient;

import java.util.HashMap;
import java.util.Map;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class StudentEndpointFragment extends BaseDataFragment {
    private static final String ARG_TITLE = "title";
    private static final String ARG_SUBTITLE = "subtitle";
    private static final String ARG_ENDPOINT = "endpoint";
    private static final long CACHE_TTL_MS = 60_000L;
    private static final int PER_PAGE = 25;
    private static final Map<String, CacheEntry> CACHE = new HashMap<>();
    private int currentPage = 1;
    private int lastPage = 1;
    private JsonObject currentProfile;

    public static StudentEndpointFragment newInstance(String title, String subtitle, String endpoint) {
        StudentEndpointFragment fragment = new StudentEndpointFragment();
        Bundle args = new Bundle();
        args.putString(ARG_TITLE, title);
        args.putString(ARG_SUBTITLE, subtitle);
        args.putString(ARG_ENDPOINT, endpoint);
        fragment.setArguments(args);
        return fragment;
    }

    public static void clearCache() {
        CACHE.clear();
    }

    @Override
    protected void configure() {
        setHeader(requireArguments().getString(ARG_TITLE), requireArguments().getString(ARG_SUBTITLE));
        addTopIconAction(R.drawable.ic_refresh, "Refresh", v -> load(true));
        if ("classes".equals(requireArguments().getString(ARG_ENDPOINT))) {
            addAction("Join Classroom", v -> openJoinForm());
            addAction("My Requests", v -> loadJoinRequests());
        } else if ("profile".equals(requireArguments().getString(ARG_ENDPOINT))) {
            addAction("Edit Profile", v -> openProfileForm());
        }
        String endpoint = requireArguments().getString(ARG_ENDPOINT);
        CacheEntry cached = CACHE.get(cacheKey(endpoint, currentPage));
        if (cached != null && !cached.isExpired()) {
            renderEndpoint(endpoint, cached.body);
            setLoading(true);
            load(false);
        } else {
            load(true);
        }
    }

    private void load(boolean showInitialLoading) {
        loadPage(currentPage, showInitialLoading);
    }

    private void loadPage(int page, boolean showInitialLoading) {
        String endpoint = requireArguments().getString(ARG_ENDPOINT);
        currentPage = Math.max(1, page);
        if (showInitialLoading && !CACHE.containsKey(cacheKey(endpoint, currentPage))) {
            clearCards();
        }
        setLoading(true);
        setStatus("", false);
        Call<JsonObject> call;
        if ("dashboard".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentDashboard();
        } else if ("profile".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentProfile();
        } else if ("classes".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentClasses();
        } else if ("announcements".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentAnnouncements(currentPage, PER_PAGE);
        } else if ("assignments".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentAssignments(currentPage, PER_PAGE);
        } else if ("grades".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentGrades(currentPage, PER_PAGE);
        } else if ("attendance".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentAttendance(currentPage, PER_PAGE);
        } else {
            setLoading(false);
            showError("Unknown student endpoint.");
            return;
        }
        track(call).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                setLoading(false);
                if (!isViewActive()) {
                    return;
                }
                if (response.isSuccessful()) {
                    if (response.body() != null) {
                        CACHE.put(cacheKey(endpoint, currentPage), new CacheEntry(response.body()));
                    }
                    renderEndpoint(endpoint, response.body());
                } else {
                    showError("Request failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                setLoading(false);
                if (isViewActive() && !call.isCanceled()) {
                    showError("Network error: " + t.getMessage());
                }
            }
        });
    }

    private void renderEndpoint(String endpoint, JsonObject body) {
        if ("dashboard".equals(endpoint)) {
            renderDashboard(body);
        } else if ("profile".equals(endpoint)) {
            renderProfile(body);
        } else {
            renderData(body, "No student records found.");
            addStudentPagination(body);
        }
    }

    private void addStudentPagination(JsonObject body) {
        if (body == null || !body.has("meta") || !body.get("meta").isJsonObject()) {
            return;
        }
        JsonObject meta = body.getAsJsonObject("meta");
        Integer pageValue = intValue(meta, "current_page");
        Integer lastValue = intValue(meta, "last_page");
        currentPage = pageValue == null ? currentPage : pageValue;
        lastPage = lastValue == null ? 1 : lastValue;
        addPaginationCard(
                currentPage,
                lastPage,
                () -> loadPage(currentPage - 1, true),
                () -> loadPage(currentPage + 1, true)
        );
    }

    private void renderDashboard(JsonObject body) {
        if (!isAdded() || getView() == null || body == null || !body.has("data")) {
            return;
        }

        JsonObject data = body.getAsJsonObject("data");
        JsonObject student = data.has("student") && data.get("student").isJsonObject()
                ? data.getAsJsonObject("student")
                : new JsonObject();

        clearCards();
        setLoading(false);
        setStatus("Overview loaded", false);

        String name = stringValue(student, "full_name");
        if (name.isEmpty()) {
            name = (stringValue(student, "first_name") + " " + stringValue(student, "last_name")).trim();
        }
        String studentNumber = stringValue(student, "student_number");
        String email = stringValue(student, "email");

        StringBuilder profile = new StringBuilder();
        if (!name.isEmpty()) profile.append(name);
        if (!studentNumber.isEmpty()) profile.append("\nStudent #: ").append(studentNumber);
        if (!email.isEmpty()) profile.append("\nEmail: ").append(email);
        addCard(profile.length() == 0 ? "Student profile unavailable." : profile.toString());

        addCard("Classes\n" + intOrZero(data, "classes_count"));
        addCard("Announcements\n" + intOrZero(data, "announcements_count"));
        addCard("Assignments\n" + intOrZero(data, "assignments_count"));
        addCard("Pending exams\n" + intOrZero(data, "pending_exams_count"));
    }

    private void renderProfile(JsonObject body) {
        if (!isAdded() || getView() == null || body == null || !body.has("data") || !body.get("data").isJsonObject()) {
            showError("Profile unavailable.");
            return;
        }
        JsonObject profile = body.getAsJsonObject("data");
        currentProfile = profile;
        clearCards();
        setLoading(false);
        setStatus("Profile loaded", false);

        StringBuilder builder = new StringBuilder();
        appendLine(builder, "Name", stringValue(profile, "full_name"));
        appendLine(builder, "Username", stringValue(profile, "username"));
        appendLine(builder, "Student ID", stringValue(profile, "student_number"));
        appendLine(builder, "Email", stringValue(profile, "email"));
        appendLine(builder, "Profile picture", stringValue(profile, "profile_image").isEmpty() ? "Not set" : stringValue(profile, "profile_image"));
        appendLine(builder, "Google", boolLabel(profile, "google_linked"));
        appendLine(builder, "GitHub", githubLabel(profile));
        addCard(builder.toString());
    }

    private void loadJoinRequests() {
        setStatus("Loading join requests...", false);
        track(ApiClient.service(requireContext()).studentJoinRequests()).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (!isAdded() || getView() == null) {
                    return;
                }
                if (!response.isSuccessful() || response.body() == null) {
                    setStatus("Join requests failed: HTTP " + response.code(), true);
                    return;
                }

                JsonObject data = response.body().getAsJsonObject("data");
                clearCards();
                boolean verified = data != null && data.has("verified") && data.get("verified").getAsBoolean();
                addCard(verified
                        ? "Verified\nGoogle and GitHub are linked."
                        : "Not verified\nLink both Google and GitHub before joining a classroom.");

                if (data != null && data.has("requests") && data.get("requests").isJsonArray()) {
                    for (com.google.gson.JsonElement element : data.getAsJsonArray("requests")) {
                        if (!element.isJsonObject()) {
                            continue;
                        }
                        JsonObject request = element.getAsJsonObject();
                        JsonObject schoolClass = request.has("school_class") && request.get("school_class").isJsonObject()
                                ? request.getAsJsonObject("school_class")
                                : new JsonObject();
                        addCard(stringValue(schoolClass, "class_name")
                                + "\n" + stringValue(schoolClass, "subject")
                                + "\nStatus: " + stringValue(request, "status"));
                    }
                }
                setStatus("Join requests loaded.", false);
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                if (isAdded() && getView() != null) {
                    setStatus("Network error: " + t.getMessage(), true);
                }
            }
        });
    }

    private void openJoinForm() {
        if (!isAdded()) {
            return;
        }

        FormDialog.show(requireContext(), "Join Classroom", new FormDialog.Field[] {
                FormDialog.text("join_code", "Classroom code", true)
        }, null, payload -> {
            setLoading(true);
            setStatus("Sending join request...", false);
            track(ApiClient.service(requireContext()).requestClassJoin(payload)).enqueue(new Callback<JsonObject>() {
                @Override
                public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                    setLoading(false);
                    if (!isAdded() || getView() == null) {
                        return;
                    }
                    if (response.isSuccessful()) {
                        setStatus("Join request sent. Waiting for teacher approval.", false);
                        clearCache();
                        load(false);
                    } else {
                        setStatus("Join request failed: HTTP " + response.code() + ". Link both Google and GitHub before joining.", true);
                    }
                }

                @Override
                public void onFailure(Call<JsonObject> call, Throwable t) {
                    setLoading(false);
                    if (isAdded() && getView() != null) {
                        setStatus("Network error: " + t.getMessage(), true);
                    }
                }
            });
        });
    }

    private void openProfileForm() {
        if (!isAdded()) {
            return;
        }
        JsonObject profile = currentProfile == null ? new JsonObject() : currentProfile;
        FormDialog.show(requireContext(), "Edit Profile", new FormDialog.Field[] {
                FormDialog.text("first_name", "First name", true),
                FormDialog.text("last_name", "Last name", true),
                FormDialog.text("email", "Email", true),
                FormDialog.text("username", "Username", true),
                FormDialog.text("profile_image", "Profile picture URL", false)
        }, profile, payload -> {
            setLoading(true);
            setStatus("Saving profile...", false);
            track(ApiClient.service(requireContext()).updateStudentProfile(payload)).enqueue(new Callback<JsonObject>() {
                @Override
                public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                    setLoading(false);
                    if (!isAdded() || getView() == null) {
                        return;
                    }
                    if (response.isSuccessful() && response.body() != null) {
                        CACHE.put(cacheKey("profile", 1), new CacheEntry(response.body()));
                        CACHE.remove(cacheKey("dashboard", 1));
                        renderProfile(response.body());
                    } else {
                        setStatus("Profile update failed: HTTP " + response.code(), true);
                    }
                }

                @Override
                public void onFailure(Call<JsonObject> call, Throwable t) {
                    setLoading(false);
                    if (isAdded() && getView() != null) {
                        setStatus("Network error: " + t.getMessage(), true);
                    }
                }
            });
        });
    }

    private int intOrZero(JsonObject object, String key) {
        return object.has(key) && !object.get(key).isJsonNull() ? object.get(key).getAsInt() : 0;
    }

    private void appendLine(StringBuilder builder, String label, String value) {
        if (value == null || value.trim().isEmpty()) {
            return;
        }
        if (builder.length() > 0) {
            builder.append('\n');
        }
        builder.append(label).append(": ").append(value);
    }

    private String boolLabel(JsonObject object, String key) {
        JsonElement value = object.get(key);
        return value != null && !value.isJsonNull() && value.getAsBoolean() ? "Linked" : "Not linked";
    }

    private String githubLabel(JsonObject profile) {
        String username = stringValue(profile, "github_username");
        return boolLabel(profile, "github_linked") + (username.isEmpty() ? "" : " (" + username + ")");
    }

    private String cacheKey(String endpoint, int page) {
        return endpoint + ":" + page;
    }

    private static final class CacheEntry {
        private final JsonObject body;
        private final long cachedAt;

        private CacheEntry(JsonObject body) {
            this.body = body;
            this.cachedAt = System.currentTimeMillis();
        }

        private boolean isExpired() {
            return System.currentTimeMillis() - cachedAt > CACHE_TTL_MS;
        }
    }
}
