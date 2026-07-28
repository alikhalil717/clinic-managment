import React from "react";
import Sidebar from "../components/dashboard/Sidebar"; // تأكد من مسار الـ Sidebar 
import Topbar from "../components/Topbar";
import StatCard from "../components/StatCard";
import RecentAppointments from "../components/RecentAppointments";
import { FiCalendar, FiClock, FiUserPlus, FiMessageCircle } from "react-icons/fi";

const SecretaryDashboard = () => {
  // إحصائيات مخصصة لمهام السكرتاريا
  const stats = [
    { id: 1, title: "Today's Appointments", value: "42", icon: <FiCalendar />, color: "#38a169" },
    { id: 2, title: "Pending Appointments", value: "12", icon: <FiClock />, color: "#d69e2e" },
    { id: 3, title: "New Patients", value: "5", icon: <FiUserPlus />, color: "#3182ce" },
    { id: 4, title: "Unread Messages", value: "3", icon: <FiMessageCircle />, color: "#e53e3e" },
  ];

  return (
    <div className="dashboard">
      <Sidebar role="secretary" />

      <div className="main-content">
        <Topbar title="Secretary Dashboard" />

        <div className="welcome-section" style={{ marginBottom: "30px" }}>
          <h2>Welcome back, Secretary</h2>
          <p>Here is your schedule and tasks for today...</p>
        </div>

        {/* أزرار الإجراءات السريعة للسكرتاريا */}
        <div className="quick-actions-container">
          <button className="quick-action-btn btn-primary">
            + Add Appointment
          </button>
          <button className="quick-action-btn btn-secondary">
            + Add Patient
          </button>
        </div>

        {/* شبكة الإحصائيات */}
        <div className="stats-grid">
          {stats.map((stat) => (
            <StatCard 
              key={stat.id}
              title={stat.title} 
              value={stat.value} 
              icon={stat.icon} 
              color={stat.color} 
            />
          ))}
        </div>

        {/* جدول المواعيد */}
        <div className="appointments-section">
           <RecentAppointments />
        </div>

      </div>
    </div>
  );
};

export default SecretaryDashboard;