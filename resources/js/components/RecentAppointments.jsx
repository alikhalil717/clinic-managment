import React from "react";

const RecentAppointments = ({ appointments }) => {
  const data = appointments || [];

  if (data.length === 0) {
    return (
      <div className="appointments-section">
        <div className="table-header">
          <h3>Recent Appointments</h3>
        </div>
        <p style={{ padding: "20px", textAlign: "center", color: "#64748b" }}>No recent appointments.</p>
      </div>
    );
  }

  return (
    <div className="appointments-section">
      <div className="table-header">
        <h3>Recent Appointments</h3>
      </div>

      <table className="appointments-table">
        <thead>
          <tr>
            <th>Patient</th>
            <th>Time</th>
            <th>Doctor</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          {data.map((appointment) => (
            <tr key={appointment.appointment_id || appointment.id}>
              <td>{appointment.patient_name || appointment.patientName}</td>
              <td>{appointment.time}</td>
              <td>{appointment.doctor_name || appointment.doctorName}</td>
              <td>
                <span className={`status-badge ${String(appointment.status || '').toLowerCase() === 'confirmed' ? 'status-confirmed' : 'status-pending'}`}>
                  {appointment.status}
                </span>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default RecentAppointments;