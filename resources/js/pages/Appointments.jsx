import React from "react";
import Sidebar from "../components/dashboard/Sidebar";
import Topbar from "../components/Topbar";
import "../styles/Appointments.css";

// بيانات وهمية لاختبار شكل الجدول
const appointmentsData = [
  { id: "A-101", patient: "Ahmed Ali", doctor: "Dr. Khaled", date: "2026-06-17", time: "09:00 AM", status: "Confirmed" },
  { id: "A-102", patient: "Sara Mohamed", doctor: "Dr. Sarah", date: "2026-06-17", time: "10:30 AM", status: "Pending" },
  { id: "A-103", patient: "Omar Hassan", doctor: "Dr. Khaled", date: "2026-06-18", time: "12:00 PM", status: "Canceled" },
  { id: "A-104", patient: "Lina Nabil", doctor: "Dr. Sarah", date: "2026-06-18", time: "02:15 PM", status: "Confirmed" }
];

export default function Appointments() {
  // دالة صغيرة لتحديد كلاس اللون بناءً على حالة الموعد
  const getStatusBadge = (status) => {
    switch (status) {
      case "Confirmed":
        return "status-confirmed";
      case "Pending":
        return "status-pending";
      case "Canceled":
        return "status-canceled";
      default:
        return "";
    }
  };

  return (
    <div className="dashboard">
      <Sidebar />

      <div className="main-content">
        <Topbar title="Appointments Management" />

        <div className="patients-page" style={{ marginTop: "30px" }}>

          {/* شريط العنوان والفلاتر (تم ربطه بكلاسات الـ CSS النظيفة) */}
          <div className="table-header">
            <h3>All Appointments</h3>

            <div className="filters-group">
              <select className="search-input">
                <option value="all">All Doctors</option>
                <option value="dr-khaled">Dr. Khaled</option>
                <option value="dr-sarah">Dr. Sarah</option>
              </select>

              <input type="date" className="search-input" />

              <button className="btn-primary">
                + Book Appointment
              </button>
            </div>
          </div>

          {/* حاوية الجدول (البطاقة البيضاء) */}
          <div className="table-container">
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
                {appointmentsData.map((appt) => (
                  <tr key={appt.id}>
                    <td>{appt.id}</td>
                    <td>{appt.patient}</td>
                    <td>{appt.doctor}</td>
                    <td>{appt.date}</td>
                    <td>{appt.time}</td>
                    <td>
                      <span className={`status-badge ${getStatusBadge(appt.status)}`}>
                        {appt.status}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  );
}