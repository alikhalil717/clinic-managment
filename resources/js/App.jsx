import React from "react";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import SecretaryWorkflowPage from "./pages/SecretaryWorkflow";
import Login from "./pages/Login";
import AdminDashboard from "./pages/AdminDashboard";
import SecretaryDashboard from "./pages/SecretaryDashboard";
import Patients from "./pages/Patients"; // استدعاء صفحة المرضى هنا
import Appointments from "./pages/Appointments"; // استدعاء صفحة المواعيد هنا
import SecretaryAppointments from "./pages/SecretaryAppointments";
import SecretaryPatients from "./pages/SecretaryPatients";
import Reports from "./pages/Reports";
import Settings from "./pages/Settings";

function App() {
  return (
    <BrowserRouter>
      <Routes>
        
        <Route path="/" element={<Login />} />
        <Route path="/admin-dashboard" element={<AdminDashboard />} />
        <Route path="/secretary-dashboard" element={<SecretaryDashboard />} />

        {/* مسار صفحة المرضى */}
        <Route path="/patients" element={<Patients />} />
        <Route path="/appointments" element={<Appointments />} />
        <Route path="/reports" element={<Reports />} />
        <Route path="/settings" element={<Settings />} />
        <Route
          path="/secretary-dashboard"
          element={<SecretaryWorkflowPage />}
        />

        {/* مسارات السكرتاريا الجديدة */}
        <Route
          path="/secretary-appointments"
          element={<SecretaryAppointments />}
        />
        <Route path="/secretary-patients" element={<SecretaryPatients />} />
        
      </Routes>
      
    </BrowserRouter>
  );
}

export default App;
