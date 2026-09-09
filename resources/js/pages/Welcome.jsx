import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { FiShield, FiLock, FiCpu, FiLayers, FiArrowRight, FiCheckCircle } from 'react-icons/fi';

export default function Welcome({ user }) {
    const features = [
        {
            icon: <FiShield className="w-6 h-6 text-amber-400" />,
            title: 'Filament Shield Security',
            description: 'Gestión granular de roles y permisos basada en Spatie Laravel-Permission.',
        },
        {
            icon: <FiCpu className="w-6 h-6 text-indigo-400" />,
            title: 'Filament v3 Admin Panel',
            description: 'Panel administrativo rápido, intuitivo y repleto de componentes potentes.',
        },
        {
            icon: <FiLayers className="w-6 h-6 text-cyan-400" />,
            title: 'Inertia.js + React 19',
            description: 'Experiencia SPA fluida combinando el poder del backend de Laravel y React.',
        },
    ];

    return (
        <>
            <Head title="Bienvenido a Ecomer" />
            <div className="min-h-screen bg-slate-950 text-slate-100 flex flex-col selection:bg-amber-500 selection:text-slate-950">
                {/* Navbar */}
                <header className="border-b border-slate-800/80 bg-slate-900/50 backdrop-blur-md sticky top-0 z-50">
                    <div className="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                        <div className="flex items-center space-x-3">
                            <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-300 flex items-center justify-center font-bold text-slate-950 shadow-lg shadow-amber-500/20">
                                E
                            </div>
                            <span className="text-xl font-extrabold tracking-tight bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent">
                                Ecomer
                            </span>
                        </div>

                        <div className="flex items-center gap-4">
                            <a
                                href="/admin"
                                className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold transition-all duration-200 shadow-md hover:shadow-amber-500/25 active:scale-95"
                            >
                                <FiLock className="w-4 h-4" />
                                <span>Panel Admin (/admin)</span>
                            </a>
                        </div>
                    </div>
                </header>

                {/* Hero Section */}
                <main className="flex-1 max-w-7xl mx-auto px-6 pt-16 pb-24 flex flex-col justify-center">
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.6 }}
                        className="text-center space-y-6 max-w-3xl mx-auto"
                    >
                        <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-amber-500/30 bg-amber-500/10 text-amber-400 text-xs font-semibold uppercase tracking-wider">
                            <FiCheckCircle /> Laravel + Filament v3 + Shield + React 19
                        </div>
                        <h1 className="text-4xl md:text-6xl font-black tracking-tight text-white leading-tight">
                            Tu plataforma de e-commerce lista para escalar
                        </h1>
                        <p className="text-slate-400 text-lg md:text-xl leading-relaxed">
                            Sistema configurado exitosamente con control de accesos por roles (Shield), administración dinámica y frontend moderno con React.
                        </p>
                        
                        <div className="pt-4 flex flex-wrap justify-center gap-4">
                            <a
                                href="/admin"
                                className="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 text-slate-950 font-bold text-base hover:opacity-95 transition-all shadow-lg shadow-amber-500/20"
                            >
                                Ingresar a Filament Admin <FiArrowRight />
                            </a>
                        </div>
                    </motion.div>

                    {/* Features Grid */}
                    <div className="grid md:grid-cols-3 gap-6 mt-20">
                        {features.map((feature, index) => (
                            <motion.div
                                key={index}
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.5, delay: 0.2 + index * 0.1 }}
                                className="p-6 rounded-2xl border border-slate-800 bg-slate-900/40 hover:border-slate-700 transition-all hover:bg-slate-900/70 group"
                            >
                                <div className="p-3 rounded-xl bg-slate-800/80 w-fit mb-4 group-hover:scale-110 transition-transform">
                                    {feature.icon}
                                </div>
                                <h3 className="text-lg font-bold text-white mb-2">{feature.title}</h3>
                                <p className="text-slate-400 text-sm leading-relaxed">{feature.description}</p>
                            </motion.div>
                        ))}
                    </div>
                </main>

                {/* Footer */}
                <footer className="border-t border-slate-800/80 py-8 text-center text-slate-500 text-sm">
                    © {new Date().getFullYear()} Ecomer. Desarrollado con Laravel, Filament, Shield e Inertia React.
                </footer>
            </div>
        </>
    );
}
