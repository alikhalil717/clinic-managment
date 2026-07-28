import React, { useState, useEffect } from "react";
import Sidebar from "../components/dashboard/Sidebar";
import Topbar from "../components/Topbar";
import { FiUsers, FiDollarSign, FiCalendar } from "react-icons/fi";
import { getDashboardStats } from "../services/adminService";
import "../styles/dashboard.css";
import "../styles/Reports.css";

export default function Reports() {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const res = await getDashboardStats();
        setStats(res.data);
      } catch (err) {
        console.error("Failed to load reports data:", err);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, []);

  if (loading) {
    return (
      <div className="dashboard">
        <Sidebar role="admin" />
        <div className="main-content">
          <Topbar title="Clinic Reports & Analytics" />
          <p style={{ padding: "40px", textAlign: "center" }}>Loading reports...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="dashboard">
      <Sidebar role="admin" />

      <div className="main-content">
        <Topbar title="Clinic Reports & Analytics" />

        <div className="reports-page" style={{ marginTop: "30px" }}>
          <div className="stats-container">
            <div className="stat-card">
              <div className="stat-icon" style={{ backgroundColor: '#e0e7ff', color: '#4f46e5' }}>
                <FiDollarSign />
              </div>
              <div className="stat-details">
                <p>Total Revenue</p>
                <h3>${stats?.stats?.total_revenue ? Number(stats.stats.total_revenue).toLocaleString() : "0"}</h3>
              </div>
            </div>

            <div className="stat-card">
              <div className="stat-icon" style={{ backgroundColor: '#dcfce7', color: '#16a34a' }}>
                <FiUsers />
              </div>
              <div className="stat-details">
                <p>Total Patients</p>
                <h3>{stats?.stats?.total_patients || "0"}</h3>
              </div>
            </div>

            <div className="stat-card">
              <div className="stat-icon" style={{ backgroundColor: '#fef08a', color: '#ca8a04' }}>
                <FiCalendar />
              </div>
              <div className="stat-details">
                <p>Total Appointments</p>
                <h3>{stats?.stats?.total_appointments || "0"}</h3>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}