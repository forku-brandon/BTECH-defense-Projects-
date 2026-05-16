from app import create_app, db
from app.models import User

app = create_app()

@app.cli.command("init-db")
def init_db():
    """Initialize the database and create a default admin user."""
    db.create_all()
    
    # Check if admin user exists
    admin = User.query.filter_by(username='admin').first()
    if not admin:
        admin = User(username='admin')
        admin.set_password('admin123')
        db.session.add(admin)
        db.session.commit()
        print("Database initialized. Default admin user created (admin / admin123)")
    else:
        print("Database already initialized.")

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
