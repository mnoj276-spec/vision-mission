import React from 'react';

export default function DataTable({ columns, data, onEdit, onDelete, idField = 'id' }) {
    if (!data || data.length === 0) {
        return (
            <div className="glass-panel p-6 rounded-2xl text-center text-gray-500">
                No data available. Add a new record to get started.
            </div>
        );
    }

    return (
        <div className="responsive-table-container glass-panel rounded-2xl overflow-hidden">
            <table className="enterprise-table w-full text-left border-collapse">
                <thead className="bg-gray-50/50">
                    <tr>
                        {columns.map((col, idx) => (
                            <th key={idx} className="p-4 border-b border-gray-200 font-semibold text-sm text-gray-700">
                                {col.header}
                            </th>
                        ))}
                        <th className="p-4 border-b border-gray-200 font-semibold text-sm text-gray-700 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {data.map((row) => (
                        <tr key={row[idField]} className="hover:bg-gray-50/50 transition-colors border-b border-gray-100 last:border-0">
                            {columns.map((col, idx) => (
                                <td key={idx} className="p-4 text-sm text-gray-800">
                                    {row[col.accessor]}
                                </td>
                            ))}
                            <td className="p-4 text-right">
                                <button 
                                    onClick={() => onEdit(row)}
                                    className="text-blue-600 hover:text-blue-800 text-sm font-medium mr-3"
                                >
                                    Edit
                                </button>
                                <button 
                                    onClick={() => {
                                        if (window.confirm('Are you sure you want to delete this record?')) {
                                            onDelete(row[idField]);
                                        }
                                    }}
                                    className="text-red-600 hover:text-red-800 text-sm font-medium"
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
