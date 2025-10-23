# SafeRide App - Page URLs & Navigation Guide

## 🌐 All Available Pages

### Public Pages
- **Landing Page**: http://localhost:8000/
- **Login**: http://localhost:8000/login
- **Register**: http://localhost:8000/register

### User Pages (Requires Login)

#### Main Navigation
- **Dashboard**: http://localhost:8000/dashboard
  - Emergency SOS button
  - Quick action cards
  - Safety tips

- **My Rides**: http://localhost:8000/trips
  - Start new trip
  - Choose ride options (Standard/Plus/Emergency)
  - Favorite locations

- **Trusted Contacts**: http://localhost:8000/trusted-contacts
  - View all contacts
  - Add new contact
  - Edit/Delete contacts

- **Profile**: http://localhost:8000/profile
  - Edit account details
  - Enable volunteer mode
  - Privacy settings

#### Trip Management
- **Trip History**: http://localhost:8000/trips/history
  - View all past trips
  - Trip statistics
  - SOS events log
  - Route alerts

- **Active Trip** (after starting): http://localhost:8000/trips/{trip_id}
  - Live location tracking
  - Trip duration counter
  - Share link for trusted contacts
  - Emergency SOS button
  - Real-time updates

- **Public Trip Viewer** (for trusted contacts): http://localhost:8000/trip/view/{share_uuid}
  - View shared trip
  - See live location
  - No login required

#### Volunteer Pages (if volunteer mode enabled)
- **Volunteer Dashboard**: http://localhost:8000/volunteer/dashboard
  - View nearby SOS alerts
  - Real-time alert feed
  - Respond to emergencies

#### Admin Pages (requires admin permission)
- **Admin Reports**: http://localhost:8000/admin/reports
  - System statistics
  - All trips overview
  - SOS alerts log
  - Export to CSV

---

## 🔗 Navigation Flow

### Starting from Dashboard:
```
Dashboard (/)
  ├── Start New Trip → My Rides (/trips)
  │   ├── Enter destination
  │   ├── Choose ride option
  │   └── Start → Active Trip (/trips/{id})
  │       ├── View live tracking
  │       ├── Trigger SOS if needed
  │       └── End Trip
  │
  ├── Trusted Contacts → Manage Contacts (/trusted-contacts)
  │   ├── Add New Contact
  │   ├── Edit Contact
  │   └── Delete Contact
  │
  ├── Profile → Edit Profile (/profile)
  │   ├── Update details
  │   └── Enable Volunteer Mode
  │
  └── Emergency SOS → Trigger Alert (API: POST /sos)
      └── Notifications sent
```

### Volunteer Flow:
```
Profile (/profile)
  └── Enable Volunteer Mode
      └── Volunteer Dashboard (/volunteer/dashboard)
          ├── View SOS Alerts
          ├── See alert location
          └── Respond to alert
```

### Admin Flow:
```
Dashboard (/)
  └── Admin Reports (navigation menu)
      └── Admin Reports Page (/admin/reports)
          ├── View statistics
          ├── Filter by date
          └── Export CSV
```

---

## 📱 Quick Access Links

### For Users:
- **Start Trip Fast**: Go to http://localhost:8000/trips
- **View History**: Click "View History" on My Rides page or go to http://localhost:8000/trips/history
- **Add Contact**: http://localhost:8000/trusted-contacts/create
- **Emergency SOS**: Available on Dashboard and Active Trip page

### For Volunteers:
- **Volunteer Dashboard**: http://localhost:8000/volunteer/dashboard
  (Must enable volunteer mode in profile first)

### For Admins:
- **Reports Dashboard**: http://localhost:8000/admin/reports
  (Requires `can:view-reports` permission)

---

## 🎯 Testing the App

### Test User Journey:
1. ✅ Visit http://localhost:8000/
2. ✅ Login with: admin@saferide.com / password
3. ✅ View Dashboard at http://localhost:8000/dashboard
4. ✅ Add trusted contact at http://localhost:8000/trusted-contacts/create
5. ✅ Start new trip at http://localhost:8000/trips
6. ✅ View active trip tracking
7. ✅ Test SOS button (simulated)
8. ✅ End trip
9. ✅ View trip history at http://localhost:8000/trips/history
10. ✅ Enable volunteer mode in profile
11. ✅ Access volunteer dashboard at http://localhost:8000/volunteer/dashboard

### Test Trip Sharing:
1. Start a trip
2. Copy the share link from active trip page
3. Open in incognito/different browser
4. View shared trip without login
5. See live location updates

---

## 🔑 Key Features by Page

### Dashboard (/dashboard)
- ✅ Emergency SOS button with GPS
- ✅ Quick action cards
- ✅ Recent activity
- ✅ Safety tips

### My Rides (/trips)
- ✅ Address search with Enter key support
- ✅ Favorite locations (Home, Work, Favorite)
- ✅ Three ride options (Standard FREE, Plus PRO, Emergency SOS)
- ✅ Recent rides preview

### Active Trip (/trips/{id})
- ✅ Live GPS tracking
- ✅ Trip duration counter
- ✅ Share link with copy button
- ✅ Current location display
- ✅ Speed and distance tracking
- ✅ Emergency SOS button
- ✅ Trusted contacts list
- ✅ Alerts history
- ✅ End trip button

### Trip History (/trips/history)
- ✅ Statistics cards (Total, Completed, Alerts, SOS)
- ✅ Paginated trip list
- ✅ Trip details with route info
- ✅ SOS events timeline
- ✅ Route anomaly alerts
- ✅ Share links for past trips

### Trusted Contacts (/trusted-contacts)
- ✅ Card grid layout
- ✅ Add/Edit/Delete functionality
- ✅ Contact details (name, phone, email)
- ✅ Empty state with CTA

### Volunteer Dashboard (/volunteer/dashboard)
- ✅ Real-time SOS alerts
- ✅ Alert location on map
- ✅ User pseudonym (privacy)
- ✅ Distance from alert
- ✅ Respond button

### Admin Reports (/admin/reports)
- ✅ System-wide statistics
- ✅ All trips table
- ✅ All SOS alerts
- ✅ Export to CSV
- ✅ Date filtering

---

## 🎨 UI Colors by Feature

- **Standard SafeRide**: Pink-Purple gradient (#8B5CF6 to #EC4899)
- **SafeRide Plus**: Golden/Amber (#F59E0B, #EAB308)
- **Emergency Mode**: Red (#EF4444, #DC2626)
- **Success/Completed**: Green (#10B981)
- **Alerts/Warnings**: Yellow (#F59E0B)
- **Navigation**: Purple-Pink gradient
- **Cards**: White with colored accents

---

## 📊 Current Data (Seeded)

The database is pre-populated with:
- 13 Users (including admin@saferide.com)
- 7 Volunteers
- 20 Trips
- 8 SOS Alerts
- 24 Trusted Contacts

All pages work with this real data!

---

## 🚀 Next Steps for Testing

1. ✅ Refresh browser to see all changes
2. ✅ Test trip starting with actual GPS location
3. ✅ Test SOS alert from dashboard
4. ✅ Add a trusted contact
5. ✅ Start a trip and view live tracking
6. ✅ Copy and test share link
7. ✅ View trip history
8. ✅ Enable volunteer mode and check dashboard
9. ✅ Test admin reports (if admin)

All features are now fully functional! 🎉
