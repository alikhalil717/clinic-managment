import React from "react";

const StatCard = ({ title, value, icon, color }) => {
  return (
    <div className="stats-card">
      <div 
        className="stats-icon-container" 
        style={{ backgroundColor: `${color}1A`, color: color }}
      >
        {icon}
      </div>
      
      <div className="stats-text-container">
        <p className="stats-title">{title}</p>
        <p className="stats-value">{value}</p>
      </div>
    </div>
  );
};

export default StatCard;