import React from "react";
import { Link, useLocation } from "react-router-dom";
import tooth from "../../assets/tooth.png";
import { FiHome, FiCalendar, FiUsers, FiFileText, FiSettings } from "react-icons/fi";

// نستقبل الـ role كـ Prop، ونعطيه قيمة افتراضية "admin" عشان ما تضرب باقي الصفحات
const Sidebar = ({ role = "admin" }) => {
  const location = useLocation();

  // تحديد مسارات الروابط بناءً على الصلاحية
  const dashboardLink = role === "secretary" ? "/secretary-dashboard" : "/admin-dashboard";
  const appointmentsLink = role === "secretary" ? "/secretary-appointments" : "/appointments";
  const patientsLink = role === "secretary" ? "/secretary-patients" : "/patients";

  return (
    <div className="sidebar">
      <div className="logo-section">
        <img src={tooth} alt="tooth" className="logo" />
        <h2>DentaPrint</h2>
      </div>

      <div className="menu">
        {/* زر الداشبورد */}
        <Link
          to={dashboardLink}
          className={`menu-item ${location.pathname === dashboardLink ? 'active' : ''}`}
          style={{ textDecoration: "none", color: "inherit" }}
        >
          <FiHome />
          <span>Dashboard</span>
        </Link>

        {/* زر المواعيد */}
        <Link
          to={appointmentsLink}
          className={`menu-item ${location.pathname === appointmentsLink ? 'active' : ''}`}
          style={{ textDecoration: "none", color: "inherit" }}
        >
          <FiCalendar />
          <span>Appointments</span>
        </Link>

        {/* زر المرضى */}
        <Link
          to={patientsLink}
          className={`menu-item ${location.pathname === patientsLink ? 'active' : ''}`}
          style={{ textDecoration: "none", color: "inherit" }}
        >
          <FiUsers />
          <span>Patients</span>
        </Link>

        {/* إخفاء الإعدادات والتقارير إذا كان الدور سكرتاريا */}
        {role === "admin" && (
          <>
            {/* 👈 تحويل التقارير إلى Link */}
            <Link
              to="/reports"
              className={`menu-item ${location.pathname === '/reports' ? 'active' : ''}`}
              style={{ textDecoration: "none", color: "inherit" }}
            >
              <FiFileText />
              <span>Reports</span>
            </Link>
            
            {/* 👈 تحويل الإعدادات إلى Link */}
            <Link
              to="/settings"
              className={`menu-item ${location.pathname === '/settings' ? 'active' : ''}`}
              style={{ textDecoration: "none", color: "inherit" }}
            >
              <FiSettings />
              <span>Settings</span>
            </Link>
          </>
        )}
      </div>
    </div>
  );
};

export default Sidebar;