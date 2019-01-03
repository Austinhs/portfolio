# To create a migration

- Create a JIRA ticket
- Create a file named "/migrations/<JIRA>.php"
- **State dependencies on any other migrations directly under the "/migrations" folder using Migrations::depend('<JIRA>')**
- Add your code to the file

# To create a checkpoint

- Create a folder named "/migrations/checkpoints/<YYYY-MM-DD>" (using the current date)
- Move all "/migrations/*.php" files inside the checkpoint

# Special checkpoints

- `0000-00-00`: This folder is **only** to be used for migrations that are being merged in from older versions of Focus. When merging a major version, move the contents of the "/migrations" folder into this checkpoint. See **Major Version Upgrades**.
- `0000-00-01`: This folder is **only** to be used for migrations that must run before all other migrations.

# Major Version Upgrades

When merging a major version into the next major version, execute the following commands prior to committing the merge:

- svn revert -R `<new_version>`/migrations
- rm -rf `<new_version>`/migrations/checkpoints/0000-00-00/*
- cp -R `<old_version>`/migrations/* `<new_version>`/migrations/checkpoints/0000-00-00
- rm `<new_version>`/migrations/checkpoints/0000-00-00/README.md
- rm -rf `<new_version>`/migrations/checkpoints/0000-00-00/manage
- svn add `<new_version>`/migrations/checkpoints/0000-00-00/*


# Add this for all Finance Migrations
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}
