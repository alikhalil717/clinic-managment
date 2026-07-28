import React from "react";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import SecretaryWorkflowPage from "./pages/SecretaryWorkflow";
import Login from "./pages/Login";
import AdminDashboard from "./pages/AdminDashboard";
import SecretaryDashboard from "./pages/SecretaryDashboard";
import Patients from "./pages/Patients";
import Appointments from "./pages/Appointments";
import Doctors from "./pages/Doctors";
import Billing from "./pages/Billing";
import SecretaryAppointments from "./pages/SecretaryAppointments";
import SecretaryPatients from "./pages/SecretaryPatients";
import Reports from "./pages/Reports";
import Settings from "./pages/Settings";
import ProtectedRoute from "./components/ProtectedRoute";

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Login />} />

        {/* Admin protected routes */}
        <Route path="/admin-dashboard" element={<ProtectedRoute allowedRole="admin"><AdminDashboard /></ProtectedRoute>} />
        <Route path="/patients" element={<ProtectedRoute allowedRole="admin"><Patients /></ProtectedRoute>} />
        <Route path="/appointments" element={<ProtectedRoute allowedRole="admin"><Appointments /></ProtectedRoute>} />
        <Route path="/doctors" element={<ProtectedRoute allowedRole="admin"><Doctors /></ProtectedRoute>} />
        <Route path="/billing" element={<ProtectedRoute allowedRole="admin"><Billing /></ProtectedRoute>} />
        <Route path="/reports" element={<ProtectedRoute allowedRole="admin"><Reports /></ProtectedRoute>} />
        <Route path="/settings" element={<ProtectedRoute allowedRole="admin"><Settings /></ProtectedRoute>} />

        {/* Secretary routes */}
        <Route path="/secretary-dashboard" element={<SecretaryDashboard />} />
        <Route path="/secretary-dashboard" element={<SecretaryWorkflowPage />} />
        <Route path="/secretary-appointments" element={<SecretaryAppointments />} />
        <Route path="/secretary-patients" element={<SecretaryPatients />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
