import React, { useState } from "react";
import LeftPanel from "../components/login/LeftPanel";
import LoginForm from "../components/login/LoginForm";
import "../styles/login.css";

const Login = () => {
  const [mode, setMode] = useState("admin");

  return (
    <div className="login-page">

      {/* زر التبديل */}
      <button
        className="switch-btn"
        onClick={() =>
          setMode(mode === "admin" ? "secretary" : "admin")
        }
      >
        {mode === "admin" ? "Secretary" : "Admin"}
      </button>

      <div className="login-container">

        <LeftPanel />

        <LoginForm mode={mode} />

      </div>
    </div>
  );
};

export default Login;