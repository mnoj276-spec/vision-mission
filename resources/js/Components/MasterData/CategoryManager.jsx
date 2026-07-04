import React, { useState, useEffect } from 'react';
import axios from 'axios';
import DataTable from './DataTable';

export default function CategoryManager() {
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [formState, setFormState] = useState({ id: null, name: '' });
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');

    const fetchCategories = async () => {
        setLoading(true);
        try {
            const res = await axios.get('/api/admin/categories');
            setCategories(res.data.data);
        } catch (err) {
            setError('Failed to fetch categories.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchCategories();
    }, []);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setSuccess('');

        try {
            if (formState.id) {
                await axios.post(`/api/admin/categories/${formState.id}`, { name: formState.name });
                setSuccess('Category updated successfully!');
            } else {
                await axios.post('/api/admin/categories', { name: formState.name });
                setSuccess('Category created successfully!');
            }
            setFormState({ id: null, name: '' });
            fetchCategories();
        } catch (err) {
            setError(err.response?.data?.message || 'An error occurred.');
        }
    };

    const handleEdit = (category) => {
        setFormState({ id: category.id, name: category.name });
        setError('');
        setSuccess('');
    };

    const handleDelete = async (id) => {
        setError('');
        setSuccess('');
        try {
            await axios.delete(`/api/admin/categories/${id}`);
            setSuccess('Category deleted successfully!');
            fetchCategories();
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to delete category.');
        }
    };

    const columns = [
        { header: 'ID', accessor: 'id' },
        { header: 'Category Name', accessor: 'name' },
        { header: 'Slug', accessor: 'slug' },
    ];

    return (
        <div className="grid grid-cols-1 md:grid-cols-[1fr_2fr] gap-8 items-start">
            <div className="glass-panel" style={{ padding: '1.5rem', borderRadius: '16px' }}>
                <h3 style={{ fontFamily: 'Outfit', fontSize: '1.25rem', marginBottom: '1.5rem', color: 'var(--accent-color)' }}>
                    {formState.id ? 'Edit Category' : 'Add Category'}
                </h3>
                
                {error && <div style={{ marginBottom: '1rem', padding: '0.75rem 1rem', background: 'rgba(239, 68, 68, 0.1)', border: '1px solid rgba(239, 68, 68, 0.2)', color: '#ef4444', borderRadius: '8px', fontSize: '0.85rem' }}>{error}</div>}
                {success && <div style={{ marginBottom: '1rem', padding: '0.75rem 1rem', background: 'rgba(16, 185, 129, 0.1)', border: '1px solid rgba(16, 185, 129, 0.2)', color: '#10b981', borderRadius: '8px', fontSize: '0.85rem' }}>{success}</div>}

                <form onSubmit={handleSubmit}>
                    <div style={{ marginBottom: '1.25rem' }}>
                        <label style={{ display: 'block', fontSize: '0.85rem', fontWeight: '600', color: 'var(--text-secondary)', marginBottom: '0.5rem' }}>Category Name</label>
                        <input 
                            type="text" 
                            className="form-control"
                            placeholder="e.g. Banking & Finance" 
                            value={formState.name}
                            onChange={(e) => setFormState({ ...formState, name: e.target.value })}
                            required
                        />
                    </div>
                    <button type="submit" className="btn-primary" style={{ width: '100%' }}>
                        {formState.id ? 'Update Category' : 'Save Category'}
                    </button>
                    {formState.id && (
                        <button 
                            type="button" 
                            onClick={() => setFormState({ id: null, name: '' })}
                            className="btn-secondary"
                            style={{ width: '100%', marginTop: '0.5rem' }}
                        >
                            Cancel Edit
                        </button>
                    )}
                </form>
            </div>

            <div>
                {loading ? (
                    <div className="text-center" style={{ padding: '2rem', color: 'var(--text-secondary)' }}>Loading categories...</div>
                ) : (
                    <DataTable 
                        columns={columns} 
                        data={categories} 
                        onEdit={handleEdit} 
                        onDelete={handleDelete} 
                    />
                )}
            </div>
        </div>
    );
}
