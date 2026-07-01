import React, { useState, useEffect } from 'react';
import axios from 'axios';
import DataTable from './DataTable';

export default function DepartmentManager() {
    const [departments, setDepartments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [formState, setFormState] = useState({ id: null, name: '', code: '' });
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');

    const fetchDepartments = async () => {
        setLoading(true);
        try {
            const res = await axios.get('/api/admin/departments');
            setDepartments(res.data.data);
        } catch (err) {
            setError('Failed to fetch departments.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchDepartments();
    }, []);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setSuccess('');

        try {
            if (formState.id) {
                await axios.post(`/api/admin/departments/${formState.id}`, { name: formState.name, code: formState.code });
                setSuccess('Department updated successfully!');
            } else {
                await axios.post('/api/admin/departments', { name: formState.name, code: formState.code });
                setSuccess('Department created successfully!');
            }
            setFormState({ id: null, name: '', code: '' });
            fetchDepartments();
        } catch (err) {
            setError(err.response?.data?.message || 'An error occurred.');
        }
    };

    const handleEdit = (dept) => {
        setFormState({ id: dept.id, name: dept.name, code: dept.code });
        setError('');
        setSuccess('');
    };

    const handleDelete = async (id) => {
        setError('');
        setSuccess('');
        try {
            await axios.delete(`/api/admin/departments/${id}`);
            setSuccess('Department deleted successfully!');
            fetchDepartments();
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to delete department.');
        }
    };

    const columns = [
        { header: 'ID', accessor: 'id' },
        { header: 'Code', accessor: 'code' },
        { header: 'Department Name', accessor: 'name' },
        { header: 'Slug', accessor: 'slug' },
    ];

    return (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
            <div className="glass-panel p-6 rounded-2xl">
                <h3 className="font-['Outfit'] text-[1.15rem] mb-4 text-[var(--accent-color)]">
                    {formState.id ? 'Edit Department' : 'Add Department'}
                </h3>
                
                {error && <div className="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">{error}</div>}
                {success && <div className="mb-4 p-3 bg-green-50 text-green-700 rounded-lg text-sm">{success}</div>}

                <form onSubmit={handleSubmit}>
                    <div className="form-group mb-4">
                        <label className="block text-sm font-medium text-gray-700 mb-1">Department Name</label>
                        <input 
                            type="text" 
                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--accent-color)] focus:border-transparent outline-none"
                            placeholder="e.g. Staff Selection Board" 
                            value={formState.name}
                            onChange={(e) => setFormState({ ...formState, name: e.target.value })}
                            required
                        />
                    </div>
                    <div className="form-group mb-4">
                        <label className="block text-sm font-medium text-gray-700 mb-1">Unique Code</label>
                        <input 
                            type="text" 
                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--accent-color)] focus:border-transparent outline-none"
                            placeholder="e.g. SSC" 
                            value={formState.code}
                            onChange={(e) => setFormState({ ...formState, code: e.target.value })}
                            required
                        />
                    </div>
                    <button type="submit" className="w-full bg-[var(--accent-color)] text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                        {formState.id ? 'Update Department' : 'Save Department'}
                    </button>
                    {formState.id && (
                        <button 
                            type="button" 
                            onClick={() => setFormState({ id: null, name: '', code: '' })}
                            className="w-full mt-2 bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition-colors"
                        >
                            Cancel Edit
                        </button>
                    )}
                </form>
            </div>

            <div className="md:col-span-2">
                {loading ? (
                    <div className="text-center p-8 text-gray-500">Loading departments...</div>
                ) : (
                    <DataTable 
                        columns={columns} 
                        data={departments} 
                        onEdit={handleEdit} 
                        onDelete={handleDelete} 
                    />
                )}
            </div>
        </div>
    );
}
