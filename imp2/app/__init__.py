from flask import Flask

def create_app():
    app = Flask(__name__)
    app.config['UPLOAD_FOLDER'] = 'snapshots/'
    app.config['JSON_SORT_KEYS'] = False  # Keep JSON responses in order

    # Register blueprints
    from app.routes import routes
    app.register_blueprint(routes)

    return app
