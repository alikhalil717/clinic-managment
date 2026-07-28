import React from "react";
import Sidebar from "../components/dashboard/Sidebar"; // تأكد من مسار الـ Sidebar عندك
import Topbar from "../components/Topbar";
import StatCard from "../components/StatCard";
import RecentAppointments from "../components/RecentAppointments";
import { FiUsers, FiCalendar, FiActivity } from "react-icons/fi";
import "../styles/dashboard.css"; // تأكد من مسار ملف الـ CSS

const AdminDashboard = () => {
  // بيانات وهمية للإحصائيات (Mock Data) ليسهل ربطها لاحقاً بـ API
  const stats = [
    { id: 1, title: "Total Patients", value: "1,520", icon: <FiUsers />, color: "#3182ce" },
    { id: 2, title: "Today's Appointments", value: "35", icon: <FiCalendar />, color: "#38a169" },
    { id: 3, title: "Active Doctors", value: "8", icon: <FiActivity />, color: "#e53e3e" },
  ];

  return (
    <div className="dashboard">
      <Sidebar />

      <div className="main-content">
        {/* استدعاء مكون الشريط العلوي بدل كتابة الكود كاملاً هنا */}
        <Topbar title="Dashboard" />

        <div className="welcome-section" style={{ marginBottom: "30px" }}>
          <h2>Welcome back, Admin</h2>
          <p>Here's what's happening .....</p>
        </div>

        {/* إضافة قسم الإحصائيات باستخدام مكون StatCard */}
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

        {/* إضافة قسم المواعيد الأخيرة */}
        <div className="appointments-section">
           <RecentAppointments />
        </div>

      </div>
    </div>
  );
};

export default AdminDashboard;