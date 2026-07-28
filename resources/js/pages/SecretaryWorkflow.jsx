import React from 'react';
import Sidebar from "../components/dashboard/Sidebar"; // تأكد من مسار الـ Sidebar عندك
import Topbar from "../components/Topbar";   // تأكد من مسار الـ Topbar عندك
import StatCard from "../components/StatCard"; // تأكد من مسار الـ StatCard عندك
import { FiCalendar, FiUserPlus, FiClock, FiActivity, FiSearch, FiCheckSquare } from "react-icons/fi";
import "../styles/dashboard.css"; // سنقوم بتحديث هذا الملف

// بيانات وهمية دقيقة بناءً على التصميم
const statCardsData = [
  { id: 1, label: "Today's Appointments", value: '28', color: '#38a169', icon: <FiCalendar /> },
  { id: 2, label: 'Checked-In Patients', value: '5 waiting', color: '#d69e2e', icon: <FiClock /> },
  { id: 3, label: 'Room Availability', value: '3 Free', color: '#3182ce', icon: <FiCheckSquare /> }, // التعديل هنا
];

const appointmentsData = [
  { time: '09:00', patient: 'أحمد خالد', patientEng: 'Ahmed Khalid', doctor: 'د. سارة', type: 'Cleaning', status: 'CHECKED IN' },
  { time: '10:00', patient: 'أحمد خالد', patientEng: 'Ahmed Khalid', doctor: 'د. سارة', type: 'Root Canal', status: 'WAITING' },
  { time: '09:00', patient: 'منى علي', patientEng: 'Mona Ali', doctor: 'د. سارة', type: 'Root Canal', status: 'CONFIRMED' },
  { time: '10:00', patient: 'منى علي', patientEng: 'Mona Ali', doctor: 'د. خالد', type: 'Root Canal', status: 'CONFIRMED' },
  { time: '10:00', patient: 'أحمد خالد', patientEng: 'Ahmed Khalid', doctor: 'د. سارة', type: 'Root Canal', status: 'CONFIRMED' },
  { time: '10:00', patient: 'منى علي', patientEng: 'Mona Ali', doctor: 'د. خالد', type: 'Root Canal', status: 'CONFIRMED' },
];

// دالة صغيرة لتحديد كلاس اللون بناءً على حالة الموعد
const getStatusBadgeClass = (status) => {
  switch (status) {
    case "CHECKED IN":
      return "status-checkedin";
    case "WAITING":
      return "status-waiting";
    case "CONFIRMED":
      return "status-confirmed";
    default:
      return "";
  }
};

export default function SecretaryWorkflowPage() {
  return (
    <div className="dashboard">
      <Sidebar />

      <div className="main-content">

        <div className="secretary-layout-wrapper">
          
          {/* شاشة لوحة تحكم السكرتاريا (القسم الأيسر) */}
          <div className="secretary-dashboard-workflow">
            <Topbar title="Secretary Dashboard" />
            
            <div className="welcome-section" style={{ marginBottom: "30px" }}>
              <h2>Welcome, Sarah</h2>
              <p>Your schedule and tasks for today...</p>
            </div>
            {/* ... باقي الأزرار والإحصائيات ... */}
          </div>

          {/* شاشة إدارة المواعيد (القسم الأيمن) */}
          <div className="appointments-management-workflow">
            <Topbar title="Appointments Management" />
            {/* ... باقي الفلاتر والجدول ... */}
          </div>

        </div> 
        {/* شاشة لوحة تحكم السكرتاريا */}
        <div className="secretary-dashboard-workflow">
          <Topbar title="Secretary Dashboard" />
          
          <div className="welcome-section" style={{ marginBottom: "30px" }}>
            <h2>Welcome, Sarah</h2>
            <p>Your schedule and tasks for today...</p>
          </div>

          {/* قسم الإجراءات السريعة */}
          <div className="quick-actions-container workflow-section">
            <button className="quick-action-btn btn-success">
              <FiCalendar /> + New Appointment
            </button>
            <button className="quick-action-btn btn-primary">
              <FiUserPlus /> + New Patient
            </button>
          </div>

          {/* شبكة الإحصائيات (نعيد استخدام .stats-grid) */}
          <div className="stats-grid workflow-section">
            {statCardsData.map((stat) => (
              <StatCard 
                key={stat.id}
                title={stat.label} 
                value={stat.value} 
                icon={stat.icon} 
                color={stat.color} 
              />
            ))}
          </div>

          {/* قسم الرسم البياني (Placeholder) */}
          <div className="chart-section workflow-section">
            <h3>Today's Overview</h3>
            <div className="chart-placeholder">
              <p>Simple Line Chart Placeholder</p>
            </div>
          </div>
        </div>

        {/* شاشة إدارة المواعيد */}
        <div className="appointments-management-workflow">
          <Topbar title="Appointments Management" />
          
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

          {/* جدول المواعيد التفصيلي (نعيد استخدام .appointments-table) */}
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