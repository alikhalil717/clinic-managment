import React from "react";
import Sidebar from "../components/dashboard/Sidebar"; 
import Topbar from "../components/Topbar";   
import { FiSearch, FiActivity } from "react-icons/fi";
import "../styles/dashboard.css"; 

const appointmentsData = [
  { time: '09:00', patient: 'أحمد خالد', patientEng: 'Ahmed Khalid', doctor: 'د. سارة', type: 'Cleaning', status: 'CHECKED IN' },
  { time: '10:00', patient: 'أحمد خالد', patientEng: 'Ahmed Khalid', doctor: 'د. سارة', type: 'Root Canal', status: 'WAITING' },
  { time: '09:00', patient: 'منى علي', patientEng: 'Mona Ali', doctor: 'د. سارة', type: 'Root Canal', status: 'CONFIRMED' },
  { time: '10:00', patient: 'منى علي', patientEng: 'Mona Ali', doctor: 'د. خالد', type: 'Root Canal', status: 'CONFIRMED' },
];

const getStatusBadgeClass = (status) => {
  switch (status) {
    case "CHECKED IN": return "status-checkedin";
    case "WAITING": return "status-waiting";
    case "CONFIRMED": return "status-confirmed";
    default: return "";
  }
};

export default function SecretaryAppointments() {
  return (
    <div className="dashboard">
      {/* 👈 تمرير الصلاحية هنا هو اللي بيخفي التقارير والإعدادات */}
      <Sidebar role="secretary" />

      <div className="main-content">
        <Topbar title="Appointments Management" />
        
        <div className="patients-page" style={{ marginTop: "30px" }}>
          {/* شريط الفلاتر */}
          <div className="table-header filters-workflow" style={{ marginBottom: "20px" }}>
            <div className="search-box">
              <FiSearch className="search-icon" />
              <input type="date" className="search-input filters-input" defaultValue="2026-06-17" />
            </div>
            
            <div className="search-box">
              <FiActivity className="search-icon" />
              <select className="search-input filters-input" defaultValue="All Doctors">
                <option value="All Doctors">All Doctors</option>
                <option value="dr-khaled">Dr. Khaled</option>
                <option value="dr-sarah">Dr. Sarah</option>
              </select>
            </div>
          </div>

          {/* جدول المواعيد التفصيلي */}
          <div className="appointments-table-container">
            <table className="appointments-table">
              <thead>
                <tr>
                  <th>Time</th>
                  <th>Patient</th>
                  <th>Doctor</th>
                  <th>Appt. Type</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {appointmentsData.map((appt, index) => (
                  <tr key={index}>
                    <td>{appt.time}</td>
                    <td>
                      <div className="patient-avatar-container">
                        <div className="patient-avatar">P</div>
                        <div className="patient-info">
                          <p className="patient-name">{appt.patient}</p>
                          <p className="patient-name-eng">{appt.patientEng}</p>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div className="patient-avatar-container">
                        <div className="patient-avatar doctor-avatar">D</div>
                        <p>{appt.doctor}</p>
                      </div>
                    </td>
                    <td>{appt.type}</td>
                    <td>
                      <span className={`status-badge badge-filled ${getStatusBadgeClass(appt.status)}`}>
                        {appt.status}
                      </span>
                    </td>
                    <td>
                      <div className="action-buttons-container">
                        <button className="btn-light btn-sm">Check In</button>
                        <button className="btn-light btn-sm">Reschedule</button>
                        <button className="btn-light btn-sm">Call Patient</button>
                        <button className="btn-subtle-danger btn-sm">Cancel</button>
                      </div>
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