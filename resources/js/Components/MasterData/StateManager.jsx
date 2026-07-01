import React, { useState, useEffect } from 'react';
import axios from 'axios';
import DataTable from './DataTable';

export default function StateManager() {
    const [states, setStates] = useState([]);
    const [loading, setLoading] = useState(true);
    const [formState, setFormState] = useState({ id: null, name: '', code: '' });
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');

    const fetchStates = async () => {
        setLoading(true);
        try {
            const res = await axios.get('/api/admin/states');
            setStates(res.data.data);
        } catch (err) {
            setError('Failed to fetch states.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchStates();
    }, []);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setSuccess('');

        try {
            if (formState.id) {
                await axios.post(`/api/admin/states/${formState.id}`, { name: formState.name, code: formState.code });
                setSuccess('State updated successfully!');
            } else {
                await axios.post('/api/admin/states', { name: formState.name, code: formState.code });
                setSuccess('State created successfully!');
            }
            setFormState({ id: null, name: '', code: '' });
            fetchStates();
        } catch (err) {
            setError(err.response?.data?.message || 'An error occurred.');
        }
    };

    const handleEdit = (stateData) => {
        setFormState({ id: stateData.id, name: stateData.name, code: stateData.code });
        setError('');
        setSuccess('');
    };

    const handleDelete = async (id) => {
        setError('');
        setSuccess('');
        try {
            await axios.delete(`/api/admin/states/${id}`);
            setSuccess('State deleted successfully!');
            fetchStates();
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to delete state.');
        }
    };

    const columns = [
        { header: 'ID', accessor: 'id' },
        { header: 'Code', accessor: 'code' },
        { header: 'State Name', accessor: 'name' },
        { header: 'Slug', accessor: 'slug' },
    ];

    return (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
            <div className="glass-panel p-6 rounded-2xl">
                <h3 className="font-['Outfit'] text-[1.15rem] mb-4 text-[var(--accent-color)]">
                    {formState.id ? 'Edit State/Region' : 'Add State/Region'}
                </h3>
                
                {error && <div className="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">{error}</div>}
                {success && <div className="mb-4 p-3 bg-green-50 text-green-700 rounded-lg text-sm">{success}</div>}

                <form onSubmit={handleSubmit}>
                    <div className="form-group mb-4">
                        <label className="block text-sm font-medium text-gray-700 mb-1">State Name</label>
                        <input 
                            type="text" 
                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--accent-color)] focus:border-transparent outline-none"
                            placeholder="e.g. Maharashtra" 
                            value={formState.name}
                            onChange={(e) => setFormState({ ...formState, name: e.target.value })}
                            required
                        />
                    </div>
                    <div className="form-group mb-4">
                        <label className="block text-sm font-medium text-gray-700 mb-1">State ISO Code</label>
                        <input 
                            type="text" 
                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--accent-color)] focus:border-transparent outline-none"
                            placeholder="e.g. MH" 
                            value={formState.code}
                            onChange={(e) => setFormState({ ...formState, code: e.target.value })}
                            required
                        />
                    </div>
                    <button type="submit" className="w-full bg-[var(--accent-color)] text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                        {formState.id ? 'Update State' : 'Save State'}
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
                    <div className="text-center p-8 text-gray-500">Loading states...</div>
                ) : (
                    <DataTable 
                        columns={columns} 
                        data={states} 
                        onEdit={handleEdit} 
                        onDelete={handleDelete} 
                    />
                )}
            </div>
        </div>
    );
}
