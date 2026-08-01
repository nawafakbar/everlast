<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index()
    {
        return view('admin.calendar');
    }

    // kalender admin
    public function getEvents()
    {
        $bookings = Booking::with('user')->whereIn('status', ['dp_paid', 'paid_in_full', 'completed'])->get();
        $dateGroups = [];

        foreach ($bookings as $b) {
            if ($b->booking_date) {
                $dateGroups[$b->booking_date][] = [
                    'start_time' => $b->start_time,
                    'end_time' => $b->end_time,
                    'label' => $b->user->name . ' (Utama: ' . Carbon::parse($b->start_time)->format('H:i') . '-' . Carbon::parse($b->end_time)->format('H:i') . ')'
                ];
            }

            if ($b->prewed_date && $b->prewed_start_time && $b->prewed_end_time) {
                $dateGroups[$b->prewed_date][] = [
                    'start_time' => $b->prewed_start_time,
                    'end_time' => $b->prewed_end_time,
                    'label' => $b->user->name . ' (Prewed: ' . Carbon::parse($b->prewed_start_time)->format('H:i') . '-' . Carbon::parse($b->prewed_end_time)->format('H:i') . ')'
                ];
            }
        }

        $events = [];

        foreach ($dateGroups as $date => $sessions) {
            $isFull = $this->checkIfFullBooked($sessions);
            
            $clientNames = [];
            foreach ($sessions as $session) {
                $clientNames[] = $session['label'];
            }

            $events[] = [
                'title' => $isFull ? 'Full Booked' : 'Partially',
                'start' => $date,
                'backgroundColor' => $isFull ? '#000000' : '#9CA3AF', 
                'borderColor' => $isFull ? '#000000' : '#9CA3AF',
                'textColor' => '#ffffff',
                'description' => implode(" | ", $clientNames), 
                'allDay' => true
            ];
        }

        return response()->json($events);
    }

    // kalendar klien
    public function getAvailableDates()
    {
        $bookings = Booking::whereIn('status', ['dp_paid', 'paid_in_full', 'completed'])->get();
        $dateGroups = [];

        foreach ($bookings as $b) {
            if ($b->booking_date) {
                $dateGroups[$b->booking_date][] = [
                    'start_time' => $b->start_time,
                    'end_time' => $b->end_time,
                    'label' => Carbon::parse($b->start_time)->format('H:i') . ' - ' . Carbon::parse($b->end_time)->format('H:i') . ' WIB'
                ];
            }

            if ($b->prewed_date && $b->prewed_start_time && $b->prewed_end_time) {
                $dateGroups[$b->prewed_date][] = [
                    'start_time' => $b->prewed_start_time,
                    'end_time' => $b->prewed_end_time,
                    'label' => Carbon::parse($b->prewed_start_time)->format('H:i') . ' - ' . Carbon::parse($b->prewed_end_time)->format('H:i') . ' WIB (Prewed)'
                ];
            }
        }

        $events = [];

        foreach ($dateGroups as $date => $sessions) {
            $isFull = $this->checkIfFullBooked($sessions);

            $bookedTimes = []; 
            foreach ($sessions as $session) {
                $bookedTimes[] = $session['label'];
            }

            $events[] = [
                'title' => $isFull ? 'Full Booked' : 'Partially',
                'start' => $date, 
                'backgroundColor' => $isFull ? '#000000' : '#9CA3AF', 
                'borderColor' => $isFull ? '#000000' : '#9CA3AF',
                'textColor' => '#ffffff',
                'description' => 'Sesi Terisi: ' . implode(", ", $bookedTimes), 
                'allDay' => true
            ];
        }

        return response()->json($events);
    }

    // fungsi pintar cek waktu kosong (gap) untuk menentukan apakah hari itu full booked atau masih ada slot kosong
    private function checkIfFullBooked($sessions)
    {
        // 1. Urutkan jadwal berdasarkan jam mulai
        usort($sessions, function($a, $b) {
            return $a['start_time'] <=> $b['start_time'];
        });

        // 2. Gabungkan jadwal yang beririsan
        $mergedSessions = [];
        foreach ($sessions as $s) {
            if (empty($mergedSessions)) {
                $mergedSessions[] = ['start' => $s['start_time'], 'end' => $s['end_time']];
            } else {
                $lastIndex = count($mergedSessions) - 1;
                $last = $mergedSessions[$lastIndex];
                
                if ($s['start_time'] <= $last['end']) {
                    if ($s['end_time'] > $last['end']) {
                        $mergedSessions[$lastIndex]['end'] = $s['end_time'];
                    }
                } else {
                    $mergedSessions[] = ['start' => $s['start_time'], 'end' => $s['end_time']];
                }
            }
        }

        // 3. Cek selisih waktu kosong (Gap)
        $isFull = true;
        $minGapHours = 3; 
        $workStart = "06:00:00"; 
        $workEnd = "18:00:00";
        
        $currentStart = $workStart;

        // Cek gap antar jadwal
        foreach ($mergedSessions as $m) {
            $gap = (strtotime($m['start']) - strtotime($currentStart)) / 3600;
            if ($gap >= $minGapHours) {
                $isFull = false;
                break;
            }
            if ($m['end'] > $currentStart) {
                $currentStart = $m['end'];
            }
        }

        // Cek gap sisa dari jadwal terakhir sampai jam tutup (18:00)
        if ($isFull) {
            $gap = (strtotime($workEnd) - strtotime($currentStart)) / 3600;
            if ($gap >= $minGapHours) {
                $isFull = false;
            }
        }

        return $isFull;
    }
}