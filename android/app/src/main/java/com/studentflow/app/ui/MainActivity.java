package com.studentflow.app.ui;

import android.content.Intent;
import android.os.Bundle;
import android.view.MenuItem;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.appcompat.app.ActionBarDrawerToggle;
import androidx.core.view.WindowCompat;
import androidx.core.view.WindowInsetsCompat;
import androidx.core.view.WindowInsetsControllerCompat;
import androidx.appcompat.app.AppCompatActivity;
import androidx.drawerlayout.widget.DrawerLayout;
import androidx.fragment.app.Fragment;

import com.google.android.material.appbar.MaterialToolbar;
import com.google.android.material.navigation.NavigationView;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import com.studentflow.app.BuildConfig;
import com.studentflow.app.R;
import com.studentflow.app.api.ApiClient;
import com.studentflow.app.data.TokenStore;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class MainActivity extends AppCompatActivity implements NavigationView.OnNavigationItemSelectedListener {
    private DrawerLayout drawerLayout;
    private MaterialToolbar toolbar;
    private TokenStore tokenStore;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        configureSystemBars();
        tokenStore = new TokenStore(this);
        if (!tokenStore.hasToken()) {
            openLogin();
            return;
        }
        if (isAdmin()) {
            tokenStore.clear();
            ApiClient.reset();
            StudentEndpointFragment.clearCache();
            openLogin();
            return;
        }
        setContentView(R.layout.activity_main);
        toolbar = findViewById(R.id.toolbar);
        drawerLayout = findViewById(R.id.drawerLayout);
        NavigationView navigationView = findViewById(R.id.navigationView);
        configureMenu(navigationView);
        configureHeader(navigationView);
        ActionBarDrawerToggle toggle = new ActionBarDrawerToggle(this, drawerLayout, toolbar, R.string.app_name, R.string.app_name);
        drawerLayout.addDrawerListener(toggle);
        toggle.syncState();
        navigationView.setNavigationItemSelectedListener(this);
        if (savedInstanceState == null) {
            navigationView.setCheckedItem(R.id.nav_dashboard);
            show("Dashboard", isStudent() ? "Overview" : "Teaching workspace", isStudent() ? StudentEndpointFragment.newInstance("Dashboard", "Your classes, announcements, assignments, and pending exams.", "dashboard") : DashboardFragment.newInstance());
        }
    }

    @Override
    public boolean onNavigationItemSelected(@NonNull MenuItem item) {
        int id = item.getItemId();
        if (id == R.id.nav_logout) {
            logout();
        } else if (id == R.id.nav_dashboard) {
            show("Dashboard", isStudent() ? "Overview" : "Teaching workspace", isStudent() ? StudentEndpointFragment.newInstance("Dashboard", "Your classes, announcements, assignments, and pending exams.", "dashboard") : DashboardFragment.newInstance());
        } else if (id == R.id.nav_classes) {
            show(isStudent() ? "My Classes" : "Classes", isStudent() ? "Current enrollment" : "Active sections", isStudent() ? StudentEndpointFragment.newInstance("My Classes", "Classes where you are currently enrolled.", "classes") : CrudFragment.newInstance("classes"));
        } else if (id == R.id.nav_students) {
            show("Students", "Roster and records", CrudFragment.newInstance("students"));
        } else if (id == R.id.nav_attendance) {
            show(isStudent() ? "My Attendance" : "Attendance", isStudent() ? "Attendance history" : "Class attendance", isStudent() ? StudentEndpointFragment.newInstance("My Attendance", "Your attendance history across classes.", "attendance") : AttendanceFragment.newInstance());
        } else if (id == R.id.nav_grades) {
            show("Grades", isStudent() ? "Academic standing" : "Gradebook", isStudent() ? StudentEndpointFragment.newInstance("Grades", "Scores synced from teacher gradebook and exams.", "grades") : GradesFragment.newInstance());
        } else if (id == R.id.nav_assignments) {
            show(isStudent() ? "My Assignments" : "Assignments", isStudent() ? "Due and submitted work" : "Coursework and submissions", isStudent() ? StudentEndpointFragment.newInstance("My Assignments", "Assignments from your enrolled classes.", "assignments") : CrudFragment.newInstance("assignments"));
        } else if (id == R.id.nav_announcements) {
            show("Announcements", isStudent() ? "Class updates" : "Broadcasts and reminders", isStudent() ? StudentEndpointFragment.newInstance("Announcements", "Class announcements for your enrolled classes.", "announcements") : CrudFragment.newInstance("announcements"));
        } else if (id == R.id.nav_exams) {
            show("Exams", isStudent() ? "Assessments" : "Exam builder", isStudent() ? StudentExamsFragment.newInstance() : TeacherExamsFragment.newInstance());
        } else if (id == R.id.nav_reports) {
            show("Reports", isStudent() ? "Performance summary" : "Analytics and exports", isStudent() ? StudentEndpointFragment.newInstance("Grades", "Scores synced from teacher gradebook and exams.", "grades") : ReportsFragment.newInstance());
        } else if (id == R.id.nav_qa_tests) {
            show("QA Tests", "Automation tools", QaTestFragment.newInstance());
        } else if (id == R.id.nav_profile) {
            show("Profile", isStudent() ? "Identity and linked accounts" : "Account settings", isStudent() ? StudentEndpointFragment.newInstance("Profile", "Your StudentFlow profile and linked social identity.", "profile") : ProfileFragment.newInstance());
        }
        drawerLayout.closeDrawers();
        return true;
    }

    private void show(String title, String subtitle, Fragment fragment) {
        toolbar.setTitle(title);
        toolbar.setSubtitle(subtitle);
        getSupportFragmentManager().beginTransaction()
                .setCustomAnimations(
                        R.anim.sf_enter,
                        R.anim.sf_exit,
                        R.anim.sf_pop_enter,
                        R.anim.sf_pop_exit
                )
                .replace(R.id.contentFrame, fragment)
                .commit();
    }

    private void logout() {
        ApiClient.service(this).logout().enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
            }
        });
        tokenStore.clear();
        ApiClient.reset();
        StudentEndpointFragment.clearCache();
        openLogin();
    }

    private void openLogin() {
        startActivity(new Intent(this, LoginActivity.class));
        overridePendingTransition(R.anim.sf_pop_enter, R.anim.sf_pop_exit);
        finish();
    }

    private void configureSystemBars() {
        WindowCompat.setDecorFitsSystemWindows(getWindow(), true);
        WindowInsetsControllerCompat controller = WindowCompat.getInsetsController(getWindow(), getWindow().getDecorView());
        controller.hide(WindowInsetsCompat.Type.statusBars());
        controller.setSystemBarsBehavior(WindowInsetsControllerCompat.BEHAVIOR_SHOW_TRANSIENT_BARS_BY_SWIPE);
        controller.setAppearanceLightNavigationBars(true);
        getWindow().setNavigationBarColor(getColor(R.color.studentflow_surface));
    }

    private void configureMenu(NavigationView navigationView) {
        boolean student = isStudent();
        navigationView.getMenu().findItem(R.id.nav_students).setVisible(!student);
        navigationView.getMenu().findItem(R.id.nav_reports).setTitle(student ? "Grades" : "Reports");
        navigationView.getMenu().findItem(R.id.nav_classes).setTitle(student ? "My Classes" : "Classes");
        navigationView.getMenu().findItem(R.id.nav_assignments).setTitle(student ? "My Assignments" : "Assignments");
        navigationView.getMenu().findItem(R.id.nav_attendance).setTitle(student ? "My Attendance" : "Attendance");
        navigationView.getMenu().findItem(R.id.nav_qa_tests).setVisible(BuildConfig.DEBUG && isAdmin());
    }

    private void configureHeader(NavigationView navigationView) {
        TextView title = navigationView.getHeaderView(0).findViewById(R.id.navHeaderTitle);
        TextView subtitle = navigationView.getHeaderView(0).findViewById(R.id.navHeaderSubtitle);
        try {
            String json = tokenStore.getUserJson();
            if (json == null) {
                return;
            }
            JsonObject user = JsonParser.parseString(json).getAsJsonObject();
            title.setText(user.get("name").getAsString());
            String role = user.get("role").getAsString();
            subtitle.setText(("student".equals(role) ? "Student workspace" : "Teacher workspace") + " • " + user.get("email").getAsString());
        } catch (RuntimeException ignored) {
        }
    }

    private boolean isStudent() {
        return hasRole("student");
    }

    private boolean isAdmin() {
        return hasRole("admin");
    }

    private boolean hasRole(String expectedRole) {
        try {
            String json = tokenStore.getUserJson();
            return json != null
                    && expectedRole.equals(JsonParser.parseString(json).getAsJsonObject().get("role").getAsString());
        } catch (RuntimeException e) {
            return false;
        }
    }
}
