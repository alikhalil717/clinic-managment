import React from "react";
import Sidebar from "../components/dashboard/Sidebar";

export default function MainLayout({ children }) {
  return (
    <div className="dashboard">
      <Sidebar />

      <div className="main-content">
        {children}
      </div>
    </div>
  );
}