import React from "react";

const RecentAppointments = ({ appointments }) => {
  const data = appointments || [
    { id: 1, patientName: 'أحمد خالد', time: '09:00 AM', doctorName: 'د. سارة', status: 'Confirmed' },
    { id: 2, patientName: 'منى علي', time: '10:30 AM', doctorName: 'د. خالد', status: 'Pending' },
  ];

  return (
    <div className="appointments-section">
      <div className="table-header">
        <h3>Recent Appointments</h3>
        <button className="btn-view-all">View All</button>
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
            <tr key={appointment.id}>
              <td>{appointment.patientName}</td>
              <td>{appointment.time}</td>
              <td>{appointment.doctorName}</td>
              <td>
                <span className={`status-badge ${appointment.status === 'Confirmed' ? 'status-confirmed' : 'status-pending'}`}>
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