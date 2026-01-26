import React, { useEffect, useState } from 'react';
import { courseService } from '../../services/api';
import { useNavigate } from 'react-router-dom';

export default function CourseList() {
    const [courses, setCourses] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const navigate = useNavigate();

    useEffect(() => {
        loadCourses();
    }, []);

    const loadCourses = async () => {
        try {
            const response = await courseService.getAll();
            setCourses(response.data['hydra:member']);
        } catch (err) {
            setError('Erreur de chargement des cours');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center h-64">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {error}
            </div>
        );
    }

    return (
        <div className="container mx-auto p-6">
            <h1 className="text-4xl font-bold mb-8 text-gray-800">Mes Cours</h1>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {courses.map(course => (
                    <div 
                        key={course.id} 
                        className="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition-shadow cursor-pointer"
                        onClick={() => navigate(`/course/${course.id}`)}
                    >
                        <h2 className="text-2xl font-semibold mb-3 text-gray-800">
                            {course.title}
                        </h2>
                        <p className="text-gray-600 mb-4">
                            {course.description.substring(0, 120)}...
                        </p>
                        <div className="flex items-center justify-between">
                            <span className="text-sm text-gray-500">
                                {course.lessons?.length || 0} leçons
                            </span>
                            <button className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                Accéder
                            </button>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}