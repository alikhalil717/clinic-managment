import React from "react";
import { FiSearch } from "react-icons/fi";

const Topbar = ({ title }) => {
  return (
    <div className="topbar">
      <h1>{title || "Dashboard"}</h1>
      
      <div className="search-box">
        <FiSearch className="search-icon" />
        <input 
          type="text" 
          placeholder="Search..." 
          className="search-input" 
        />
      </div>
    </div>
  );
};

export default Topbar;