import React, { useState, useEffect } from "react";
import Sidebar from "../components/dashboard/Sidebar";
import Topbar from "../components/Topbar";
import { getAppointments } from "../services/adminService";
import "../styles/Appointments.css";

const getStatusBadge = (status) => {
  switch (status?.toLowerCase()) {
    case "confirmed":
    case "completed":
    case "finished":
      return "status-confirmed";
    case "pending":
      return "status-pending";
    case "ongoing":
      return "status-active";
    case "cancelled":
    case "canceled":
    case "rejected":
      return "status-canceled";
    default:
      return "";
  }
};

export default function Appointments() {
  const [appointments, setAppointments] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchAppointments = async () => {
      try {
        const res = await getAppointments();
        setAppointments(res.data || []);
      } catch (err) {
        console.error("Failed to load appointments:", err);
      } finally {
        setLoading(false);
      }
    };
    fetchAppointments();
  }, []);

  return (
    <div className="dashboard">
      <Sidebar />

      <div className="main-content">
        <Topbar title="Appointments Management" />

        <div className="patients-page" style={{ marginTop: "30px" }}>
          <div className="table-header">
            <h3>All Appointments</h3>
          </div>

          <div className="table-container">
            {loading ? (
              <p style={{ padding: "20px", textAlign: "center" }}>Loading appointments...</p>
            ) : appointments.length === 0 ? (
              <p style={{ padding: "20px", textAlign: "center" }}>No appointments found.</p>
            ) : (
              <table className="appointments-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Patient Name</th>
                    <th>Doctor</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  {appointments.map((appt) => (
                    <tr key={appt.appointment_id}>
                      <td>{appt.appointment_id}</td>
                      <td>{appt.patient?.name || "N/A"}</td>
                      <td>{appt.doctor?.name || "N/A"}</td>
                      <td>{appt.date}</td>
                      <td>{appt.start_time}</td>
                      <td>
                        <span className={`status-badge ${getStatusBadge(appt.status)}`}>
                          {appt.status}
                        </span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}