import React from "react";
import Sidebar from "../components/dashboard/Sidebar"; 
import Topbar from "../components/Topbar";   
import "../styles/dashboard.css"; // 👈 هذا السطر هو اللي رح يصلح شكل الصفحة!

const patientsData = [
  { id: 1, name: "Ahmed Ali", age: 28, phone: "0999999999", condition: "Tooth Cleaning" },
  { id: 2, name: "Sara Mohamed", age: 34, phone: "0988888888", condition: "Root Canal" },
  { id: 3, name: "Omar Hassan", age: 22, phone: "0977777777", condition: "Braces" }
];

export default function Patients() {
  return (
    <div className="dashboard">
      <Sidebar />

      <div className="main-content">
        <Topbar title="Patients Management" />

        <div className="patients-page" style={{ marginTop: "30px" }}>
          <div className="table-header" style={{ marginBottom: "20px" }}>
            <h2>Patients List</h2>
            <button className="btn-primary" style={{ padding: "10px 20px", borderRadius: "8px" }}>
              + Add Patient
            </button>
          </div>

          <div className="appointments-table-container">
            <table className="appointments-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Age</th>
                  <th>Phone</th>
                  <th>Condition</th>
                </tr>
              </thead>
              <tbody>
                {patientsData.map((patient) => (
                  <tr key={patient.id}>
                    <td>{patient.id}</td>
                    <td>{patient.name}</td>
                    <td>{patient.age}</td>
                    <td>{patient.phone}</td>
                    <td>{patient.condition}</td>
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