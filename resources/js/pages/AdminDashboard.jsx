import React, { useState, useEffect } from "react";
import Sidebar from "../components/dashboard/Sidebar";
import Topbar from "../components/Topbar";
import StatCard from "../components/StatCard";
import RecentAppointments from "../components/RecentAppointments";
import { FiUsers, FiCalendar, FiActivity, FiDollarSign } from "react-icons/fi";
import { getDashboardStats } from "../services/adminService";
import "../styles/dashboard.css";

const AdminDashboard = () => {
  const [stats, setStats] = useState(null);
  const [recentAppts, setRecentAppts] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const res = await getDashboardStats();
        const data = res.data;
        setStats(data.stats);
        setRecentAppts(data.recent_appointments || []);
      } catch (err) {
        console.error("Failed to load dashboard:", err);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, []);

  const statCards = [
    { id: 1, title: "Total Patients", value: stats?.total_patients ?? "...", icon: <FiUsers />, color: "#3182ce" },
    { id: 2, title: "Total Appointments", value: stats?.total_appointments ?? "...", icon: <FiCalendar />, color: "#38a169" },
    { id: 3, title: "Active Doctors", value: stats?.total_doctors ?? "...", icon: <FiActivity />, color: "#e53e3e" },
    { id: 4, title: "Total Revenue", value: stats?.total_revenue ? `$${Number(stats.total_revenue).toLocaleString()}` : "...", icon: <FiDollarSign />, color: "#805ad5" },
  ];

  if (loading) {
    return (
      <div className="dashboard">
        <Sidebar />
        <div className="main-content">
          <Topbar title="Dashboard" />
          <p style={{ padding: "40px", textAlign: "center" }}>Loading dashboard...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="dashboard">
      <Sidebar />

      <div className="main-content">
        <Topbar title="Dashboard" />

        <div className="welcome-section" style={{ marginBottom: "30px" }}>
          <h2>Welcome back, Admin</h2>
          <p>Here's what's happening today.</p>
        </div>

        <div className="stats-grid">
          {statCards.map((stat) => (
            <StatCard
              key={stat.id}
              title={stat.title}
              value={stat.value}
              icon={stat.icon}
              color={stat.color}
            />
          ))}
        </div>

        <div className="appointments-section">
          <RecentAppointments appointments={recentAppts} />
        </div>
      </div>
    </div>
  );
};

export default AdminDashboard;