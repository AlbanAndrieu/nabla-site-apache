export default function SiteFooter() {
	return (
		<footer className="footer" role="contentinfo">
			<div className="social-links">
				<a
					href="https://www.linkedin.com/in/nabla"
					target="_blank"
					rel="noopener noreferrer"
					className="social-link"
					aria-label="LinkedIn"
				>
					<i className="fab fa-linkedin-in" aria-hidden="true" />
				</a>
				<a
					href="https://calendly.com/alban-andrieu"
					target="_blank"
					rel="noopener noreferrer"
					className="social-link"
					aria-label="Calendly"
				>
					<i className="fa fa-calendar-plus" aria-hidden="true" />
				</a>
				<a
					href="https://github.com/AlbanAndrieu"
					target="_blank"
					rel="noopener noreferrer"
					className="social-link"
					aria-label="GitHub"
				>
					<i className="fab fa-github" aria-hidden="true" />
				</a>
				<a
					href="https://hub.docker.com/u/nabla"
					target="_blank"
					rel="noopener noreferrer"
					className="social-link"
					aria-label="Docker Hub"
				>
					<i className="fab fa-docker" aria-hidden="true" />
				</a>
				<a
					href="https://stackexchange.com/users/4652074/albanandrieu"
					target="_blank"
					rel="noopener noreferrer"
					className="social-link"
					aria-label="Stack Exchange"
				>
					<i className="fab fa-stack-exchange" aria-hidden="true" />
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
