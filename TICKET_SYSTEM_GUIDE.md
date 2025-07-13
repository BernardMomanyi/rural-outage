# 🎫 Ticket System User Guide

## Overview
The OutageSys ticket system provides a complete support ticket management solution for users, technicians, and administrators. Each user role has specific capabilities and access levels.

## 🚀 Quick Start

### For Users
1. **Submit a Ticket**: Navigate to "Submit Ticket" in the sidebar
2. **View Your Tickets**: Click "My Tickets" to see all your submitted tickets
3. **Track Progress**: Check ticket status and assigned technician contact information

### For Technicians  
1. **View Assigned Tickets**: Access "My Assigned Tickets" from the sidebar
2. **Update Status**: Change ticket status (pending → in progress → resolved)
3. **Contact Users**: Use provided user contact information for updates

### For Administrators
1. **Manage All Tickets**: Access "Manage Tickets" from the admin dashboard
2. **Assign Technicians**: Assign tickets to available technicians
3. **Monitor System**: View statistics and manage ticket workflow

## 📋 User Role Capabilities

### 👤 Regular Users
- **Submit new tickets** with detailed descriptions
- **View personal ticket history** and current status
- **See assigned technician contact information** for direct communication
- **Track ticket progress** through status updates
- **Export personal ticket data** for record keeping

**Available Pages:**
- `submit_ticket.php` - Create new support tickets
- `my_tickets.php` - View and manage personal tickets

### 🔧 Technicians
- **View assigned tickets** only (role-based access)
- **Update ticket status** (pending → in progress → resolved)
- **Access user contact information** for direct communication
- **View detailed ticket information** including user details
- **Manage workload** through status updates

**Available Pages:**
- `technician_tickets.php` - View and manage assigned tickets

### 👑 Administrators
- **View all tickets** in the system regardless of assignment
- **Assign tickets to technicians** with contact information
- **Update ticket status** and priority levels
- **Monitor system statistics** and ticket distribution
- **Manage ticket workflow** and technician assignments
- **Export ticket data** for reporting and analysis

**Available Pages:**
- `admin_tickets.php` - Complete ticket management interface

## 🎯 Ticket Workflow

### 1. Ticket Creation (User)
```
User submits ticket → System generates ticket number → Ticket status: "pending"
```

### 2. Ticket Assignment (Admin)
```
Admin reviews ticket → Assigns to technician → Ticket status: "assigned"
```

### 3. Ticket Processing (Technician)
```
Technician updates status → "in_progress" → "resolved" → "closed"
```

### 4. Communication Flow
```
User ←→ Technician (direct contact via provided information)
Admin ←→ All parties (system management)
```

## 📊 Ticket Categories & Priorities

### Categories
- **Technical** - System issues, login problems, technical support
- **Billing** - Payment issues, invoice questions, account charges
- **Service** - Service upgrades, plan changes, feature requests
- **General** - General inquiries, information requests
- **Outage** - Power outages, service disruptions, emergency issues

### Priorities
- **Urgent** - Critical issues requiring immediate attention
- **High** - Important issues affecting service
- **Medium** - Standard support requests
- **Low** - General inquiries and non-critical issues

## 🔧 Technical Features

### Database Structure
The ticket system uses a comprehensive database schema with:
- Unique ticket numbers (TKT + Year + Month + Random)
- User and technician contact information
- Status tracking with timestamps
- Priority and category classification
- Assignment tracking

### API Endpoints
- `GET /api/tickets.php` - Retrieve tickets (role-based filtering)
- `POST /api/tickets.php` - Create/update tickets and assignments
- Role-based access control for all operations

### Security Features
- Session-based authentication
- Role-based access control
- Input validation and sanitization
- SQL injection prevention
- XSS protection

## 🎨 User Interface Features

### Modern Design
- Responsive design for all devices
- Clean, intuitive interface
- Color-coded priority and status indicators
- Smooth animations and transitions

### Interactive Elements
- Real-time status updates
- Filter and search capabilities
- Export functionality
- Modal dialogs for detailed views
- Notification system for user feedback

### Accessibility
- Keyboard navigation support
- Screen reader compatibility
- High contrast color schemes
- Responsive text sizing

## 📱 Mobile Compatibility

All ticket system pages are fully responsive and work on:
- Desktop computers
- Tablets
- Mobile phones
- Various screen sizes and orientations

## 🔄 Status Management

### Ticket Statuses
1. **Pending** - New ticket awaiting assignment
2. **Assigned** - Ticket assigned to technician
3. **In Progress** - Technician actively working on ticket
4. **Resolved** - Issue resolved, awaiting user confirmation
5. **Closed** - Ticket completed and closed

### Status Transitions
- Users can only view their tickets
- Technicians can update status of assigned tickets
- Admins can manage all tickets and statuses

## 📞 Communication Features

### User-Technician Communication
- Direct contact information provided
- Email and phone number sharing
- Real-time status updates
- Ticket history tracking

### System Notifications
- Status change notifications
- Assignment notifications
- Completion confirmations
- Export and reporting features

## 🛠️ Troubleshooting

### Common Issues
1. **Can't submit ticket**: Check form validation and required fields
2. **Tickets not loading**: Verify database connection and permissions
3. **Status not updating**: Ensure proper role permissions
4. **Contact info missing**: Check user profile completion

### Support
- Check browser console for JavaScript errors
- Verify database connectivity
- Review server error logs
- Test with different user roles

## 🚀 Getting Started

1. **Login** to the system with appropriate role
2. **Navigate** to ticket-related pages via sidebar
3. **Create** or **view** tickets based on your role
4. **Use** the interactive features for ticket management
5. **Export** data as needed for reporting

## 📈 Best Practices

### For Users
- Provide detailed descriptions when submitting tickets
- Include relevant contact information
- Check ticket status regularly
- Contact assigned technician directly when needed

### For Technicians
- Update ticket status promptly
- Communicate with users directly
- Document progress in ticket descriptions
- Resolve tickets efficiently

### For Administrators
- Assign tickets to appropriate technicians
- Monitor system performance
- Review ticket statistics regularly
- Maintain user and technician information

---

**✅ The ticket system is fully functional and ready for production use!** 