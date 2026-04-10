export default function SiteFooter() {
	return (
		<footer className="footer">
			<div className="social-links">
				<a
					href="https://www.linkedin.com/in/nabla"
					target="_blank"
					rel="noopener noreferrer"
					className="social-link"
				>
					<span className="visually-hidden">LinkedIn</span>
					<i className="fab fa-linkedin-in" />
				</a>
				<a
					href="https://calendly.com/alban-andrieu"
					target="_blank"
					rel="noopener noreferrer"
					className="social-link"
				>
					<span className="visually-hidden">Calendly</span>
					<i className="fa fa-calendar-plus" />
				</a>
				<a
					href="https://github.com/AlbanAndrieu"
					target="_blank"
					rel="noopener noreferrer"
					className="social-link"
				>
					<span className="visually-hidden">GitHub</span>
					<i className="fab fa-github" />
				</a>
				<a
					href="https://hub.docker.com/u/nabla"
					target="_blank"
					rel="noopener noreferrer"
					className="social-link"
				>
					<span className="visually-hidden">Docker Hub</span>
					<i className="fab fa-docker" />
				</a>
				<a
					href="https://stackexchange.com/users/4652074/albanandrieu"
					target="_blank"
					rel="noopener noreferrer"
					className="social-link"
				>
					<span className="visually-hidden">Stack Exchange</span>
					<i className="fab fa-stack-exchange" />
				</a>
			</div>
			<div className="footer-links">
				<a href="/policy/legal">Legal notices</a>
			</div>
			<p className="text-md-center mt-3">
				<a
					href="#top"
					className="btn btn-sm btn-outline-secondary"
					aria-label="Back to top of page"
				>
					Back to top
				</a>
			</p>
			<p className="footer-copyright" />
		</footer>
	);
}
