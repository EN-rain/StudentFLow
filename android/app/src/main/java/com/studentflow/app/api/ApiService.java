package com.studentflow.app.api;

import com.google.gson.JsonObject;
import com.studentflow.app.models.ChangePasswordRequest;
import com.studentflow.app.models.LoginRequest;
import com.studentflow.app.models.LoginResponse;

import retrofit2.Call;
import retrofit2.http.Body;
import retrofit2.http.DELETE;
import retrofit2.http.GET;
import retrofit2.http.POST;
import retrofit2.http.PATCH;
import retrofit2.http.Path;
import retrofit2.http.PUT;
import retrofit2.http.Query;

public interface ApiService {
    @POST("auth/login")
    Call<LoginResponse> login(@Body LoginRequest request);

    @POST("auth/register")
    Call<LoginResponse> register(@Body JsonObject request);

    @POST("auth/google")
    Call<LoginResponse> googleLogin(@Body JsonObject request);

    @POST("auth/github")
    Call<LoginResponse> githubLogin(@Body JsonObject request);

    @POST("auth/mobile-exchange")
    Call<LoginResponse> mobileExchange(@Body JsonObject request);

    @POST("auth/logout")
    Call<JsonObject> logout();

    @GET("auth/me")
    Call<JsonObject> me();

    @POST("auth/change-password")
    Call<JsonObject> changePassword(@Body ChangePasswordRequest request);

    @POST("auth/forgot-password")
    Call<JsonObject> forgotPassword(@Body JsonObject request);

    @GET("classes")
    Call<JsonObject> classes();

    @GET("admin/teachers")
    Call<JsonObject> adminTeachers();

    @GET("admin/settings")
    Call<JsonObject> adminSettings();

    @GET("admin/activity-logs")
    Call<JsonObject> adminActivityLogs();

    @POST("classes")
    Call<JsonObject> createClass(@Body JsonObject request);

    @PUT("classes/{classId}")
    Call<JsonObject> updateClass(@Path("classId") int classId, @Body JsonObject request);

    @DELETE("classes/{classId}")
    Call<JsonObject> deleteClass(@Path("classId") int classId);

    @GET("students")
    Call<JsonObject> students(@Query("q") String query, @Query("class_id") Integer classId);

    @POST("students")
    Call<JsonObject> createStudent(@Body JsonObject request);

    @PUT("students/{studentId}")
    Call<JsonObject> updateStudent(@Path("studentId") int studentId, @Body JsonObject request);

    @DELETE("students/{studentId}")
    Call<JsonObject> deleteStudent(@Path("studentId") int studentId);

    @GET("attendance")
    Call<JsonObject> attendance(@Query("class_id") Integer classId, @Query("date") String date);

    @POST("attendance")
    Call<JsonObject> saveAttendance(@Body JsonObject request);

    @POST("attendance/mark-all-present")
    Call<JsonObject> markAllPresent(@Body JsonObject request);

    @PUT("attendance/{attendanceId}")
    Call<JsonObject> updateAttendance(@Path("attendanceId") int attendanceId, @Body JsonObject request);

    @DELETE("attendance/{attendanceId}")
    Call<JsonObject> deleteAttendance(@Path("attendanceId") int attendanceId);

    @GET("assignments")
    Call<JsonObject> assignments();

    @POST("assignments")
    Call<JsonObject> createAssignment(@Body JsonObject request);

    @PUT("assignments/{assignmentId}")
    Call<JsonObject> updateAssignment(@Path("assignmentId") int assignmentId, @Body JsonObject request);

    @DELETE("assignments/{assignmentId}")
    Call<JsonObject> deleteAssignment(@Path("assignmentId") int assignmentId);

    @GET("announcements")
    Call<JsonObject> announcements();

    @POST("announcements")
    Call<JsonObject> createAnnouncement(@Body JsonObject request);

    @PUT("announcements/{announcementId}")
    Call<JsonObject> updateAnnouncement(@Path("announcementId") int announcementId, @Body JsonObject request);

    @DELETE("announcements/{announcementId}")
    Call<JsonObject> deleteAnnouncement(@Path("announcementId") int announcementId);

    @GET("classes/{classId}/grade-categories")
    Call<JsonObject> gradeCategories(@Path("classId") int classId);

    @POST("classes/{classId}/grade-categories")
    Call<JsonObject> createGradeCategory(@Path("classId") int classId, @Body JsonObject request);

    @GET("classes/{classId}/grade-items")
    Call<JsonObject> gradeItems(@Path("classId") int classId);

    @POST("classes/{classId}/grade-items")
    Call<JsonObject> createGradeItem(@Path("classId") int classId, @Body JsonObject request);

    @POST("classes/{classId}/students/{studentId}/student-grades")
    Call<JsonObject> saveStudentGrades(@Path("classId") int classId, @Path("studentId") int studentId, @Body JsonObject request);

    @GET("classes/{classId}/students/{studentId}/final-grade")
    Call<JsonObject> finalGrade(@Path("classId") int classId, @Path("studentId") int studentId);

    @GET("student/dashboard")
    Call<JsonObject> studentDashboard();

    @GET("student/profile")
    Call<JsonObject> studentProfile();

    @PATCH("student/profile")
    Call<JsonObject> updateStudentProfile(@Body JsonObject request);

    @GET("student/classes")
    Call<JsonObject> studentClasses();

    @GET("student/announcements")
    Call<JsonObject> studentAnnouncements();

    @GET("student/assignments")
    Call<JsonObject> studentAssignments();

    @GET("student/grades")
    Call<JsonObject> studentGrades();

    @GET("student/attendance")
    Call<JsonObject> studentAttendance();

    @GET("student/exams")
    Call<JsonObject> studentExams();

    @GET("student/join-requests")
    Call<JsonObject> studentJoinRequests();

    @POST("student/join-requests")
    Call<JsonObject> requestClassJoin(@Body JsonObject request);

    @GET("classes/{classId}/join-requests")
    Call<JsonObject> classJoinRequests(@Path("classId") int classId);

    @PATCH("join-requests/{requestId}")
    Call<JsonObject> reviewClassJoinRequest(@Path("requestId") int requestId, @Body JsonObject request);

    @POST("student/exams/{attemptId}/start")
    Call<JsonObject> startStudentExam(@Path("attemptId") int attemptId);

    @POST("student/exams/{attemptId}/submit")
    Call<JsonObject> submitStudentExam(@Path("attemptId") int attemptId, @Body JsonObject request);

    @POST("exam/magic/{token}/start")
    Call<JsonObject> startMagicExam(@Path("token") String token);

    @GET("exam/magic/{token}")
    Call<JsonObject> magicExam(@Path("token") String token);

    @POST("exam/magic/{token}/submit")
    Call<JsonObject> submitMagicExam(@Path("token") String token, @Body JsonObject request);

    @GET("exams")
    Call<JsonObject> exams();

    @POST("exams")
    Call<JsonObject> createExam(@Body JsonObject request);

    @POST("exams/{examId}/publish")
    Call<JsonObject> publishExam(@Path("examId") int examId);

    @GET("exams/{examId}/audit")
    Call<JsonObject> examAudit(@Path("examId") int examId);

    @GET("reports/{type}")
    Call<JsonObject> report(@Path("type") String type, @Query("class_id") Integer classId, @Query("student_id") Integer studentId);
}
