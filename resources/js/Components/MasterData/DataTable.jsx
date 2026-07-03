import React from 'react';

export default function DataTable({ columns, data, onEdit, onDelete, idField = 'id' }) {
    if (!data || data.length === 0) {
        return (
            <div className="glass-panel text-center" style={{ padding: '2rem', borderRadius: '16px', color: 'var(--text-secondary)' }}>
                No data available. Add a new record to get started.
            </div>
        );
    }

    return (
        <div className="responsive-table-container glass-panel" style={{ borderRadius: '16px', overflow: 'hidden', padding: '1rem' }}>
            <table className="enterprise-table density-comfortable">
                <thead>
                    <tr>
                        {columns.map((col, idx) => (
                            <th key={idx}>
                                {col.header}
                            </th>
                        ))}
                        <th className="text-right" style={{ textAlign: 'right' }}>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {data.map((row) => (
                        <tr key={row[idField]}>
                            {columns.map((col, idx) => (
                                <td key={idx}>
                                    {row[col.accessor]}
                                </td>
                            ))}
                            <td className="action-column text-right" style={{ textAlign: 'right', whiteSpace: 'nowrap' }}>
                                <button 
                                    onClick={() => onEdit(row)}
                                    className="btn-sm-view"
                                    style={{ marginRight: '0.5rem' }}
                                >
                                    Edit
                                </button>
                                <button 
                                    onClick={() => {
                                        if (window.confirm('Are you sure you want to delete this record?')) {
                                            onDelete(row[idField]);
                                        }
                                    }}
                                    className="btn-sm-danger"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
