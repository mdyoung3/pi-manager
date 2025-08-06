ARG BASE_IMAGE
FROM $BASE_IMAGE

# Update npm to latest version (optional but recommended)
RUN npm install -g npm@latest

# Install your global npm packages
RUN npm install -g @anthropic-ai/claude-code

# You can also install multiple packages in one command
# RUN npm install -g package1 package2 package3

# Optional: Verify installation
RUN npm list -g --depth=0
