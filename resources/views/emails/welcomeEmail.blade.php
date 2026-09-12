<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Welcome to Link Up</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Helvetica Neue', Arial, sans-serif;
      background-color: #f9fafb;
      color: #333;
    }

    .container {
      max-width: 600px;
      margin: 40px auto;
      background: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    }

    .header {
      background: linear-gradient(135deg, #ec4899, #6366f1);
      color: #ffffff;
      text-align: center;
      padding: 50px 20px;
    }

    .header h1 {
      margin: 0;
      font-size: 30px;
      letter-spacing: 1px;
    }

    .body {
      padding: 35px 25px;
      line-height: 1.6;
      font-size: 16px;
      text-align: center;
    }

    .body h2 {
      color: #111827;
      margin-top: 0;
      font-size: 22px;
    }

    .body p {
      margin: 15px 0;
    }

    .btn {
      display: inline-block;
      margin: 25px 0;
      padding: 14px 28px;
      background-color: #ec4899;
      color: #ffffff !important;
      text-decoration: none;
      border-radius: 10px;
      font-weight: bold;
      font-size: 16px;
    }

    .btn:hover {
      background-color: #db2777;
    }

    .features {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      margin-top: 30px;
    }

    .feature-box {
      flex: 1 1 45%;
      background: #f3f4f6;
      margin: 10px;
      padding: 20px;
      border-radius: 12px;
      font-size: 14px;
    }

    .feature-box h3 {
      margin: 0 0 10px;
      font-size: 16px;
      color: #4f46e5;
    }

    .footer {
      background: #f9fafb;
      text-align: center;
      padding: 20px;
      font-size: 14px;
      color: #6b7280;
    }

    @media (max-width: 600px) {
      .header h1 {
        font-size: 24px;
      }

      .body {
        padding: 25px 15px;
        font-size: 15px;
      }

      .features {
        flex-direction: column;
      }

      .feature-box {
        flex: 1 1 100%;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <!-- Header -->
    <div class="header">
      <h1>✨ Welcome to Link Up!</h1>
    </div>

    <!-- Body -->
    <div class="body">
      <h2>Hello {{ $user->first_name }},</h2>
      <p>
        You’ve just joined <strong>Link Up</strong> — where connections happen both online & offline.
        Meet new people, discover events, and grab tickets to experiences you’ll love.
      </p>

      <div class="features">
        <div class="feature-box">
          <h3>💖 <br> Connect</h3>
          <p>Match with amazing people who share your vibes and interests.</p>
        </div>
        <div class="feature-box">
          <h3>🎟️ <br> Events</h3>
          <p>Discover exclusive events near you and secure your tickets instantly.</p>
        </div>
        <div class="feature-box">
          <h3>🔥 <br> Trending</h3>
          <p>Stay in the loop with what’s hot and happening right now.</p>
        </div>
        <div class="feature-box">
          <h3>🤝 <br> Meet Up</h3>
          <p>Turn online sparks into real-world memories with ease.</p>
        </div>
      </div>

      <p style="margin-top: 30px;">
        Ready to Link Up and make unforgettable moments?
      </p>
        <a href="{{ route('user.home') }}" class="btn">Start Exploring</a>
    </div>

    <!-- Sponsor -->
    @include('emails.partials.sponsor')

    <!-- Footer -->
    <div class="footer">
      <p>&copy; {{ date('Y') }} Link Up. All rights reserved.</p>
      <p>You are receiving this email because you signed up on Link Up.</p>
    </div>
  </div>
</body>

</html>
