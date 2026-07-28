import React from "react";
import tooth from "../../assets/tooth.png";

const LeftPanel = () => {
  return (
    <div className="left-panel">

      <div className="overlay"></div>

      <div className="brand-content">

        <img src={tooth} alt="tooth" className="logo" />

        <h1>DentaPrint</h1>

        

      </div>

    </div>
  );
};

export default LeftPanel;