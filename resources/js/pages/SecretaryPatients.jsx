import React from "react";
import Sidebar from "../components/dashboard/Sidebar";
import Topbar from "../components/Topbar";
import {
  FiSearch,
  FiPlus,
  FiPhone,
  FiInfo,
  FiCheckCircle,
} from "react-icons/fi";
import "../styles/dashboard.css";

const patientsData = [
  {
    id: "P-201",
    name: "أحمد خالد",
    nameEng: "Ahmed Khalid",
    phone: "+11712345636",
    age: 29,
    gender: "Male",
    lastVisit: "2026-06-15",
    type: "Cleaning",
    status: "Active",
  },
  {
    id: "P-202",
    name: "أحمد خالد",
    nameEng: "Ahmed Khalid",
    phone: "+11712345637",
    age: 30,
    gender: "Female",
    lastVisit: "2026-06-15",
    type: "Cleaning",
    status: "Checked In",
  },
  {
    id: "P-203",
    name: "أحمد خالد",
    nameEng: "Ahmed Khalid",
    phone: "+11712345636",
    age: 29,
    gender: "Male",
    lastVisit: "2026-06-15",
    type: "Cleaning",
    status: "Checked In",
  },
  {
    id: "P-204",
    name: "منى علي",
    nameEng: "Mona Ali",
    phone: "+11712345637",
    age: 30,
    gender: "Female",
    lastVisit: "2026-06-15",
    type: "Cleaning",
    status: "In-Treatment",
  },
  {
    id: "P-205",
    name: "منى علي",
    nameEng: "Mona Ali",
    phone: "+11712345637",
    age: 29,
    gender: "Female",
    lastVisit: "2026-06-15",
    type: "Cleaning",
    status: "In-Treatment",
  },
  {
    id: "P-206",
    name: "منى علي",
    nameEng: "Mona Ali",
    phone: "+11712345637",
    age: 29,
    gender: "Female",
    lastVisit: "2026-06-15",
    type: "Cleaning",
    status: "Active",
  },
];

const getStatusBadgeClass = (status) => {
  switch (status) {
    case "Active":
      return "status-active";
    case "Checked In":
      return "status-checkedin";
    case "In-Treatment":
      return "status-intreatment";
    default:
      return "";
  }
};

export default function SecretaryPatients() {
  return (
    <div className="dashboard">
      <Sidebar role="secretary" />

      <div className="main-content">
        <Topbar title="Patients Management" />

        <div className="patients-page" style={{ marginTop: "30px" }}>
          {/* قسم الإجراءات السريعة (بعد حذف الترحيب) */}
          <div
            className="page-header-actions"
            style={{ justifyContent: "flex-end" }}
          >
            <div className="quick-actions-row">
              <div className="action-group">
                <div style={{ display: "flex", gap: "15px" }}>
                  {/* تم حذف زر إضافة المريض من هنا */}
                  <div className="search-box" style={{ margin: 0 }}>
                    <FiSearch
                      className="search-icon"
                      style={{ left: "10px" }}
                    />
                    <input
                      type="text"
                      placeholder="Search by name or phone..."
                      className="search-input"
                      style={{ paddingLeft: "35px", width: "250px" }}
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* الفلاتر */}
          <div
            className="table-header filters-workflow"
            style={{ marginBottom: "20px", marginTop: "20px" }}
          >
            <h3 style={{ margin: 0, marginRight: "20px" }}>Filters</h3>
            <select className="search-input filters-input">
              <option value="All Statuses">All Statuses</option>
              <option value="Active">Active</option>
              <option value="Checked In">Checked In</option>
            </select>

            <select className="search-input filters-input">
              <option value="All Insurance">All Insurance</option>
              <option value="Uninsured">Uninsured</option>
            </select>
          </div>

          {/* جدول المرضى */}
          <div className="appointments-table-container">
            <table className="appointments-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Patient Name</th>
                  <th>Phone</th>
                  <th>Age</th>
                  <th>Gender</th>
                  <th>Last Visit</th>
                  <th>Type</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {patientsData.map((patient, index) => (
                  <tr key={index}>
                    <td style={{ fontWeight: "bold", color: "#475569" }}>
                      {patient.id}
                    </td>
                    <td>
                      <div className="patient-avatar-container">
                        <div
                          className="patient-avatar"
                          style={{ backgroundColor: "#0ea5e9" }}
                        >
                          {patient.nameEng.charAt(0)}
                        </div>
                        <div className="patient-info">
                          <p className="patient-name">{patient.name}</p>
                          <p className="patient-name-eng">{patient.nameEng}</p>
                        </div>
                      </div>
                    </td>
                    <td>{patient.phone}</td>
                    <td>{patient.age}</td>
                    <td>{patient.gender}</td>
                    <td>
                      <div
                        style={{
                          display: "flex",
                          flexDirection: "column",
                          fontSize: "13px",
                        }}
                      >
                        <span>{patient.lastVisit}</span>
                      </div>
                    </td>
                    <td>{patient.type}</td>
                    <td>
                      <span
                        className={`status-badge badge-filled ${getStatusBadgeClass(patient.status)}`}
                      >
                        {patient.status}
                      </span>
                    </td>
                    <td>
                      <div className="action-buttons-container">
                        <button
                          className="btn-primary btn-sm"
                          style={{
                            display: "flex",
                            alignItems: "center",
                            gap: "5px",
                          }}
                        >
                          <FiCheckCircle /> Check In
                        </button>
                        <button
                          className="btn-light btn-sm"
                          style={{
                            display: "flex",
                            alignItems: "center",
                            gap: "5px",
                          }}
                        >
                          <FiPhone /> Call
                        </button>
                        <button
                          className="btn-light btn-sm"
                          style={{
                            display: "flex",
                            alignItems: "center",
                            gap: "5px",
                          }}
                        >
                          <FiInfo /> Details
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>

            {/* أزرار التنقل بين الصفحات */}
            <div className="pagination">
              <button className="page-btn text-btn">&lt; Previous</button>
              <button className="page-btn active">1</button>
              <button className="page-btn">2</button>
              <button className="page-btn">3</button>
              <button className="page-btn">4</button>
              <button className="page-btn text-btn">Next &gt;</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
