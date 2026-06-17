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
        ActionBarDrawerToggle toggle = new ActionBarDrawerToggle(this, drawerLayout, toolbar, R.string.app_name, R.string.app_name);
        drawerLayout.addDrawerListener(toggle);
        toggle.syncState();
        navigationView.setNavigationItemSelectedListener(this);
        if (savedInstanceState == null) {
            navigationView.setCheckedItem(R.id.nav_dashboard);
            show("Dashboard", DashboardFragment.newInstance());
        }
    }

    @Override
    public boolean onNavigationItemSelected(@NonNull MenuItem item) {
        int id = item.getItemId();
        if (id == R.id.nav_logout) {
            logout();
        } else if (id == R.id.nav_dashboard) {
            show("Dashboard", DashboardFragment.newInstance());
        } else if (id == R.id.nav_classes) {
            show("Classes", CrudFragment.newInstance("classes"));
        } else if (id == R.id.nav_students) {
            show("Students", CrudFragment.newInstance("students"));
        } else if (id == R.id.nav_attendance) {
            show("Attendance", AttendanceFragment.newInstance());
        } else if (id == R.id.nav_grades) {
            show("Grades", GradesFragment.newInstance());
        } else if (id == R.id.nav_assignments) {
            show("Assignments", CrudFragment.newInstance("assignments"));
        } else if (id == R.id.nav_announcements) {
            show("Announcements", CrudFragment.newInstance("announcements"));
        } else if (id == R.id.nav_reports) {
            show("Reports", ReportsFragment.newInstance());
        } else if (id == R.id.nav_profile) {
            show("Profile", ProfileFragment.newInstance());
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
}
