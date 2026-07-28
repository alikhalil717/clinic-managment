import React from "react";
import { FiSearch, FiLogOut } from "react-icons/fi";
import { useNavigate } from "react-router-dom";
import { logoutUser, getUserRole } from "../services/authService";

const Topbar = ({ title }) => {
  const navigate = useNavigate();

  const handleLogout = async () => {
    const role = getUserRole();
    await logoutUser(role);
    navigate("/");
  };

  return (
    <div className="topbar">
      <h1>{title || "Dashboard"}</h1>

      <div style={{ display: "flex", alignItems: "center", gap: "15px" }}>
        <div className="search-box">
          <FiSearch className="search-icon" />
          <input
            type="text"
            placeholder="Search..."
            className="search-input"
          />
        </div>
        <button
          onClick={handleLogout}
          style={{
            background: "none",
            border: "1px solid #e2e8f0",
            borderRadius: "8px",
            padding: "8px 12px",
            cursor: "pointer",
            display: "flex",
            alignItems: "center",
            gap: "6px",
            color: "#64748b",
          }}
          title="Logout"
        >
          <FiLogOut /> Logout
        </button>
      </div>
    </div>
  );
};

export default Topbar;