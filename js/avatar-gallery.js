// Avatar Gallery Component
class AvatarGallery {
  constructor(containerId, options = {}) {
    this.container = document.getElementById(containerId);
    this.options = {
      onSelect: () => {},
      onUpload: () => {},
      allowCustom: true,
      allowDragDrop: true,
      ...options
    };
    
    this.avatars = [
      { id: 'avatar1', src: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjUwIiBjeT0iMzUiIHI9IjE1IiBmaWxsPSIjM0I4MkY2Ii8+CjxwYXRoIGQ9Ik0yMCA4MEMyMCA2NS4zNzIgMzEuMzcyIDU0IDQ2IDU0SDU0QzY4LjYyOCA1NCA4MCA2NS4zNzIgODAgODBWNzBIMjBWOThaIiBmaWxsPSIjM0I4MkY2Ii8+Cjwvc3ZnPgo=' },
      { id: 'avatar2', src: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjUwIiBjeT0iMzUiIHI9IjE1IiBmaWxsPSIjRjU5RTAiIi8+CjxwYXRoIGQ9Ik0yMCA4MEMyMCA2NS4zNzIgMzEuMzcyIDU0IDQ2IDU0SDU0QzY4LjYyOCA1NCA4MCA2NS4zNzIgODAgODBWNzBIMjBWOThaIiBmaWxsPSIjRjU5RTAiIi8+Cjwvc3ZnPgo=' },
      { id: 'avatar3', src: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjUwIiBjeT0iMzUiIHI9IjE1IiBmaWxsPSIjMTBCOTgxIiIvPgo8cGF0aCBkPSJNMjAgODBDMjAgNjUuMzcyIDMxLjM3MiA1NCA0NiA1NEg1NEM2OC42MjggNTQgODAgNjUuMzcyIDgwIDgwVjcwSDIwVjk4WiIgZmlsbD0iIzEwQjk4MSIvPgo8L3N2Zz4K' },
      { id: 'avatar4', src: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjUwIiBjeT0iMzUiIHI9IjE1IiBmaWxsPSIjRUM0ODk5IiIvPgo8cGF0aCBkPSJNMjAgODBDMjAgNjUuMzcyIDMxLjM3MiA1NCA0NiA1NEg1NEM2OC42MjggNTQgODAgNjUuMzcyIDgwIDgwVjcwSDIwVjk4WiIgZmlsbD0iI0VDNDg5OSIvPgo8L3N2Zz4K' },
      { id: 'avatar5', src: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjUwIiBjeT0iMzUiIHI9IjE1IiBmaWxsPSIjOEI1Q0Y2IiIvPgo8cGF0aCBkPSJNMjAgODBDMjAgNjUuMzcyIDMxLjM3MiA1NCA0NiA1NEg1NEM2OC42MjggNTQgODAgNjUuMzcyIDgwIDgwVjcwSDIwVjk4WiIgZmlsbD0iIzhCNUNGNiIvPgo8L3N2Zz4K' },
      { id: 'avatar6', src: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjUwIiBjeT0iMzUiIHI9IjE1IiBmaWxsPSIjRjU5NDI0IiIvPgo8cGF0aCBkPSJNMjAgODBDMjAgNjUuMzcyIDMxLjM3MiA1NCA0NiA1NEg1NEM2OC42MjggNTQgODAgNjUuMzcyIDgwIDgwVjcwSDIwVjk4WiIgZmlsbD0iI0Y1OTQyNCIvPgo8L3N2Zz4K' },
      { id: 'avatar7', src: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjUwIiBjeT0iMzUiIHI9IjE1IiBmaWxsPSIjRkY2QjIwIiIvPgo8cGF0aCBkPSJNMjAgODBDMjAgNjUuMzcyIDMxLjM3MiA1NCA0NiA1NEg1NEM2OC42MjggNTQgODAgNjUuMzcyIDgwIDgwVjcwSDIwVjk4WiIgZmlsbD0iI0ZGNkIyMCIvPgo8L3N2Zz4K' },
      { id: 'avatar8', src: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjUwIiBjeT0iMzUiIHI9IjE1IiBmaWxsPSIjRkY0NDQ0IiIvPgo8cGF0aCBkPSJNMjAgODBDMjAgNjUuMzcyIDMxLjM3MiA1NCA0NiA1NEg1NEM2OC42MjggNTQgODAgNjUuMzcyIDgwIDgwVjcwSDIwVjk4WiIgZmlsbD0iI0ZGNDQ0NCIvPgo8L3N2Zz4K' }
    ];
    
    this.selectedAvatar = null;
    this.init();
  }
  
  init() {
    this.render();
    this.bindEvents();
  }
  
  render() {
    this.container.innerHTML = `
      <div class="avatar-gallery">
        <div class="avatar-grid">
          ${this.avatars.map(avatar => `
            <div class="avatar-item" data-avatar-id="${avatar.id}">
              <img src="${avatar.src}" alt="Avatar" class="avatar-img">
              <div class="avatar-overlay">
                <i class="fa fa-check"></i>
              </div>
            </div>
          `).join('')}
        </div>
        ${this.options.allowCustom ? `
          <div class="avatar-upload-section">
            <div class="avatar-upload-area" id="dragDropArea">
              <i class="fa fa-cloud-upload-alt"></i>
              <p>Drag & drop image here or click to upload</p>
              <input type="file" id="avatarUpload" accept="image/*" style="display: none;">
            </div>
          </div>
        ` : ''}
      </div>
    `;
  }
  
  bindEvents() {
    // Avatar selection
    this.container.querySelectorAll('.avatar-item').forEach(item => {
      item.addEventListener('click', () => {
        this.selectAvatar(item.dataset.avatarId);
      });
    });
    
    // File upload
    if (this.options.allowCustom) {
      const uploadArea = this.container.querySelector('#dragDropArea');
      const fileInput = this.container.querySelector('#avatarUpload');
      
      uploadArea.addEventListener('click', () => fileInput.click());
      
      fileInput.addEventListener('change', (e) => {
        this.handleFileUpload(e.target.files[0]);
      });
      
      if (this.options.allowDragDrop) {
        uploadArea.addEventListener('dragover', (e) => {
          e.preventDefault();
          uploadArea.classList.add('drag-over');
        });
        
        uploadArea.addEventListener('dragleave', () => {
          uploadArea.classList.remove('drag-over');
        });
        
        uploadArea.addEventListener('drop', (e) => {
          e.preventDefault();
          uploadArea.classList.remove('drag-over');
          this.handleFileUpload(e.dataTransfer.files[0]);
        });
      }
    }
  }
  
  selectAvatar(avatarId) {
    // Remove previous selection
    this.container.querySelectorAll('.avatar-item').forEach(item => {
      item.classList.remove('selected');
    });
    
    // Add selection to clicked item
    const selectedItem = this.container.querySelector(`[data-avatar-id="${avatarId}"]`);
    if (selectedItem) {
      selectedItem.classList.add('selected');
    }
    
    // Get avatar data
    const avatar = this.avatars.find(a => a.id === avatarId);
    this.selectedAvatar = avatar;
    
    // Call callback
    this.options.onSelect(avatar);
  }
  
  handleFileUpload(file) {
    if (!file) return;
    
    if (!file.type.startsWith('image/')) {
      this.showError('Please select an image file');
      return;
    }
    
    const reader = new FileReader();
    reader.onload = (e) => {
      const customAvatar = {
        id: 'custom',
        src: e.target.result,
        name: file.name
      };
      
      this.selectedAvatar = customAvatar;
      this.options.onUpload(customAvatar);
      this.showSuccess('Custom avatar uploaded successfully!');
    };
    
    reader.readAsDataURL(file);
  }
  
  showSuccess(message) {
    // Create notification
    const notification = document.createElement('div');
    notification.className = 'notification success';
    notification.innerHTML = `
      <i class="fa fa-check-circle"></i>
      <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
      notification.remove();
    }, 3000);
  }
  
  showError(message) {
    // Create notification
    const notification = document.createElement('div');
    notification.className = 'notification error';
    notification.innerHTML = `
      <i class="fa fa-exclamation-circle"></i>
      <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
      notification.remove();
    }, 3000);
  }
  
  getSelectedAvatar() {
    return this.selectedAvatar;
  }
  
  setSelectedAvatar(avatarId) {
    this.selectAvatar(avatarId);
  }
}

// CSS for avatar gallery
const avatarGalleryStyles = `
  .avatar-gallery {
    padding: 1rem;
  }
  
  .avatar-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
  }
  
  .avatar-item {
    position: relative;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    cursor: pointer;
    overflow: hidden;
    border: 3px solid transparent;
    transition: all 0.3s ease;
  }
  
  .avatar-item:hover {
    transform: scale(1.05);
    border-color: #ec4899;
  }
  
  .avatar-item.selected {
    border-color: #ec4899;
    box-shadow: 0 0 0 3px rgba(236,72,153,0.2);
  }
  
  .avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  
  .avatar-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(236,72,153,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
  }
  
  .avatar-item.selected .avatar-overlay {
    opacity: 1;
  }
  
  .avatar-overlay i {
    color: white;
    font-size: 1.5rem;
  }
  
  .avatar-upload-section {
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
  }
  
  .avatar-upload-area {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
  }
  
  .avatar-upload-area:hover,
  .avatar-upload-area.drag-over {
    border-color: #ec4899;
    background: rgba(236,72,153,0.05);
  }
  
  .avatar-upload-area i {
    font-size: 2rem;
    color: #6b7280;
    margin-bottom: 0.5rem;
  }
  
  .avatar-upload-area p {
    color: #6b7280;
    margin: 0;
  }
  
  .notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    color: white;
    font-weight: 600;
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    animation: slideIn 0.3s ease;
  }
  
  .notification.success {
    background: #10b981;
  }
  
  .notification.error {
    background: #ef4444;
  }
  
  @keyframes slideIn {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
`;

// Add styles to document
if (!document.getElementById('avatar-gallery-styles')) {
  const styleSheet = document.createElement('style');
  styleSheet.id = 'avatar-gallery-styles';
  styleSheet.textContent = avatarGalleryStyles;
  document.head.appendChild(styleSheet);
} 