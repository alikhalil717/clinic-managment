import React, { useState, useEffect } from "react";
import Sidebar from "../components/dashboard/Sidebar";
import Topbar from "../components/Topbar";
import { getDoctors } from "../services/adminService";
import "../styles/dashboard.css";

export default function Doctors() {
  const [doctors, setDoctors] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchDoctors = async () => {
      try {
        const res = await getDoctors();
        setDoctors(res.data || []);
      } catch (err) {
        console.error("Failed to load doctors:", err);
      } finally {
        setLoading(false);
      }
    };
    fetchDoctors();
  }, []);

  return (
    <div className="dashboard">
      <Sidebar />

      <div className="main-content">
        <Topbar title="Doctors Management" />

        <div className="patients-page" style={{ marginTop: "30px" }}>
          <div className="table-header" style={{ marginBottom: "20px" }}>
            <h2>Doctors List</h2>
          </div>

          <div className="appointments-table-container">
            {loading ? (
              <p style={{ padding: "20px", textAlign: "center" }}>Loading doctors...</p>
            ) : doctors.length === 0 ? (
              <p style={{ padding: "20px", textAlign: "center" }}>No doctors found.</p>
            ) : (
              <table className="appointments-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Specialization</th>
                    <th>Experience</th>
                    <th>Rating</th>
                  </tr>
                </thead>
                <tbody>
                  {doctors.map((doctor) => (
                    <tr key={doctor.doctor_id}>
                      <td>{doctor.doctor_id}</td>
                      <td>{doctor.first_name} {doctor.last_name}</td>
                      <td>{doctor.email}</td>
                      <td>{doctor.phone}</td>
                      <td>{doctor.specialization}</td>
                      <td>{doctor.years_of_experience} yrs</td>
                      <td>{doctor.rating} ({doctor.reviews_count} reviews)</td>
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