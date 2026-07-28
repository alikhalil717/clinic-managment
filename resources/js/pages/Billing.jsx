import React, { useState, useEffect } from "react";
import Sidebar from "../components/dashboard/Sidebar";
import Topbar from "../components/Topbar";
import { getDashboardStats } from "../services/adminService";
import { FiDollarSign } from "react-icons/fi";
import "../styles/dashboard.css";

export default function Billing() {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const res = await getDashboardStats();
        setStats(res.data?.stats || null);
      } catch (err) {
        console.error("Failed to load billing data:", err);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, []);

  return (
    <div className="dashboard">
      <Sidebar />

      <div className="main-content">
        <Topbar title="Billing & Payments" />

        <div className="patients-page" style={{ marginTop: "30px" }}>
          <div className="table-header" style={{ marginBottom: "20px" }}>
            <h2>Revenue Overview</h2>
          </div>

          {loading ? (
            <p style={{ padding: "20px", textAlign: "center" }}>Loading billing data...</p>
          ) : (
            <div className="stats-grid">
              <div className="stat-card" style={{ padding: "20px", borderRadius: "12px", background: "white", border: "1px solid #e2e8f0" }}>
                <div className="stat-icon" style={{ backgroundColor: '#e0e7ff', color: '#4f46e5' }}>
                  <FiDollarSign />
                </div>
                <div className="stat-details">
                  <p>Total Revenue</p>
                  <h3>${stats?.total_revenue ? Number(stats.total_revenue).toLocaleString() : "0"}</h3>
                </div>
              </div>
              <div className="stat-card" style={{ padding: "20px", borderRadius: "12px", background: "white", border: "1px solid #e2e8f0" }}>
                <div className="stat-icon" style={{ backgroundColor: '#dcfce7', color: '#16a34a' }}>
                  <FiDollarSign />
                </div>
                <div className="stat-details">
                  <p>Treatment Plans</p>
                  <h3>{stats?.total_treatment_plans || "0"}</h3>
                </div>
              </div>
              <div className="stat-card" style={{ padding: "20px", borderRadius: "12px", background: "white", border: "1px solid #e2e8f0" }}>
                <div className="stat-icon" style={{ backgroundColor: '#fef08a', color: '#ca8a04' }}>
                  <FiDollarSign />
                </div>
                <div className="stat-details">
                  <p>Total Appointments</p>
                  <h3>{stats?.total_appointments || "0"}</h3>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}