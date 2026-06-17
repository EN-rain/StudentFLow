package com.studentflow.app.ui;

import android.content.Intent;
import android.os.Bundle;
import android.view.MenuItem;

import androidx.annotation.NonNull;
import androidx.appcompat.app.ActionBarDrawerToggle;
import androidx.appcompat.app.AppCompatActivity;
import androidx.drawerlayout.widget.DrawerLayout;
import androidx.fragment.app.Fragment;

import com.google.android.material.appbar.MaterialToolbar;
import com.google.android.material.navigation.NavigationView;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
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
        tokenStore = new TokenStore(this);
        if (!tokenStore.hasToken()) {
            openLogin();
            return;
        }
        setContentView(R.layout.activity_main);
        toolbar = findViewById(R.id.toolbar);
        drawerLayout = findViewById(R.id.drawerLayout);
        NavigationView navigationView = findViewById(R.id.navigationView);
        configureMenu(navigationView);
        ActionBarDrawerToggle toggle = new ActionBarDrawerToggle(this, drawerLayout, toolbar, R.string.app_name, R.string.app_name);
        drawerLayout.addDrawerListener(toggle);
        toggle.syncState();
        navigationView.setNavigationItemSelectedListener(this);
        if (savedInstanceState == null) {
            navigationView.setCheckedItem(R.id.nav_dashboard);
            show("Dashboard", isStudent() ? StudentEndpointFragment.newInstance("Dashboard", "Your classes, announcements, assignments, and pending exams.", "dashboard") : DashboardFragment.newInstance());
        }
    }

    @Override
    public boolean onNavigationItemSelected(@NonNull MenuItem item) {
        int id = item.getItemId();
        if (id == R.id.nav_logout) {
            logout();
        } else if (id == R.id.nav_dashboard) {
            show("Dashboard", isStudent() ? StudentEndpointFragment.newInstance("Dashboard", "Your classes, announcements, assignments, and pending exams.", "dashboard") : DashboardFragment.newInstance());
        } else if (id == R.id.nav_classes) {
            show(isStudent() ? "My Classes" : "Classes", isStudent() ? StudentEndpointFragment.newInstance("My Classes", "Classes where you are currently enrolled.", "classes") : CrudFragment.newInstance("classes"));
        } else if (id == R.id.nav_students) {
            show("Students", CrudFragment.newInstance("students"));
        } else if (id == R.id.nav_attendance) {
            show(isStudent() ? "My Attendance" : "Attendance", isStudent() ? StudentEndpointFragment.newInstance("My Attendance", "Your attendance history across classes.", "attendance") : AttendanceFragment.newInstance());
        } else if (id == R.id.nav_grades) {
            show("Grades", isStudent() ? StudentEndpointFragment.newInstance("Grades", "Scores synced from teacher gradebook and exams.", "grades") : GradesFragment.newInstance());
        } else if (id == R.id.nav_assignments) {
            show(isStudent() ? "My Assignments" : "Assignments", isStudent() ? StudentEndpointFragment.newInstance("My Assignments", "Assignments from your enrolled classes.", "assignments") : CrudFragment.newInstance("assignments"));
        } else if (id == R.id.nav_announcements) {
            show("Announcements", isStudent() ? StudentEndpointFragment.newInstance("Announcements", "Class announcements for your enrolled classes.", "announcements") : CrudFragment.newInstance("announcements"));
        } else if (id == R.id.nav_exams) {
            show("Exams", isStudent() ? StudentExamsFragment.newInstance() : TeacherExamsFragment.newInstance());
        } else if (id == R.id.nav_reports) {
            show("Reports", isStudent() ? StudentEndpointFragment.newInstance("Grades", "Scores synced from teacher gradebook and exams.", "grades") : ReportsFragment.newInstance());
        } else if (id == R.id.nav_qa_tests) {
            show("QA Tests", QaTestFragment.newInstance());
        } else if (id == R.id.nav_profile) {
            show("Profile", isStudent() ? StudentEndpointFragment.newInstance("Profile", "Your StudentFlow profile and linked social identity.", "profile") : ProfileFragment.newInstance());
        }
        drawerLayout.closeDrawers();
        return true;
    }

    private void show(String title, Fragment fragment) {
        toolbar.setTitle(title);
        getSupportFragmentManager().beginTransaction()
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
        openLogin();
    }

    private void openLogin() {
        startActivity(new Intent(this, LoginActivity.class));
        finish();
    }

    private void configureMenu(NavigationView navigationView) {
        boolean student = isStudent();
        navigationView.getMenu().findItem(R.id.nav_students).setVisible(!student);
        navigationView.getMenu().findItem(R.id.nav_reports).setTitle(student ? "Grades" : "Reports");
        navigationView.getMenu().findItem(R.id.nav_classes).setTitle(student ? "My Classes" : "Classes");
        navigationView.getMenu().findItem(R.id.nav_assignments).setTitle(student ? "My Assignments" : "Assignments");
        navigationView.getMenu().findItem(R.id.nav_attendance).setTitle(student ? "My Attendance" : "Attendance");
    }

    private boolean isStudent() {
        try {
            String json = tokenStore.getUserJson();
            return json != null && JsonParser.parseString(json).getAsJsonObject().get("role").getAsString().equals("student");
        } catch (RuntimeException e) {
            return false;
        }
    }
}
